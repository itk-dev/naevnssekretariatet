<?php

namespace App\Service;

use App\Entity\Document;
use App\Exception\DocumentDirectoryException;
use App\Exception\FileMovingException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\String\Slugger\SluggerInterface;

class DocumentUploader
{
    /**
     * @var SluggerInterface
     */
    private $slugger;
    private $documentDirectory;

    /**
     * @throws DocumentDirectoryException
     */
    public function __construct(SluggerInterface $slugger, string $documentDirectory, Filesystem $filesystem)
    {
        $this->checkDocumentDirectory($documentDirectory, $filesystem);
        $this->slugger = $slugger;
        $this->documentDirectory = $documentDirectory;
    }

    public function upload(UploadedFile $file): string
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

        // AsciiSlugger handles spaces, special chars and danish specific letters
        $safeFilename = $this->slugger->slug($originalFilename);

        // Make a safe and unique filename
        $newFilename = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();

        try {
            $file->move(
                $this->getDirectory(),
                $newFilename
            );
        } catch (FileException $e) {
            throw new FileMovingException($e->getMessage());
        }

        return $newFilename;
    }

    public function getDirectory(): string
    {
        return $this->documentDirectory;
    }

    public function handleDownload(Document $document): Response
    {
        $filepath = $this->documentDirectory.$document->getFilename();
        $response = new BinaryFileResponse($filepath);
        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            $document->getFilename()
        );

        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }

    /**
     * @throws DocumentDirectoryException
     */
    private function checkDocumentDirectory(string $documentDirectory, Filesystem $filesystem)
    {
        if (!$filesystem->exists($documentDirectory)) {
            $message = sprintf('Document directory %s does not exist.', $documentDirectory);
            throw new DocumentDirectoryException($message);
        }
    }
}
