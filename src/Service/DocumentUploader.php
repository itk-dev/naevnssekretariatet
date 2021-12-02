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
     * @var Filesystem
     */
    private $filesystem;

    public function __construct(SluggerInterface $slugger, string $documentDirectory, Filesystem $filesystem)
    {
        $this->documentDirectory = $documentDirectory;
        $this->filesystem = $filesystem;
        $this->slugger = $slugger;
    }

    /**
     * @throws DocumentDirectoryException
     */
    public function specifyDirectory(string $directory)
    {
        $this->documentDirectory = $this->documentDirectory.$directory;
        $this->checkDocumentDirectory($this->documentDirectory, $this->filesystem);
    }

    /**
     * Uploads document.
     *
     * @throws FileMovingException
     */
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

    /**
     * Downloads document.
     */
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
     * Ensures document directory exists.
     *
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
