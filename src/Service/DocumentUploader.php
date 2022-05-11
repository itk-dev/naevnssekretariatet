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
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\String\Slugger\SluggerInterface;

class DocumentUploader
{
    /**
     * @var SluggerInterface
     * @var SluggerInterface
     */
    private $slugger;
    private $uploadDocumentDirectory;

    /**
     * @var Filesystem
     */
    private $filesystem;
    private $projectDirectory;

    public function __construct(SluggerInterface $slugger, string $uploadDocumentDirectory, string $projectDirectory, Filesystem $filesystem)
    {
        $this->projectDirectory = $projectDirectory;
        $this->uploadDocumentDirectory = $uploadDocumentDirectory;
        $this->filesystem = $filesystem;
        $this->slugger = $slugger;
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
                $this->getFullDirectory(),
                $newFilename
            );
        } catch (FileException $e) {
            throw new FileMovingException($e->getMessage());
        }

        return $newFilename;
    }

    /**
     * Downloads document.
     */
    public function handleDownload(Document $document, bool $forceDownload = true): Response
    {
        $filepath = $this->getFilepath($document->getFilename());

        if ($forceDownload) {
            // @see https://symfonycasts.com/screencast/symfony-uploads/file-streaming
            $response = new StreamedResponse(function () use ($filepath) {
                $outputStream = fopen('php://output', 'wb');
                $fileStream = fopen($filepath, 'r');
                stream_copy_to_stream($fileStream, $outputStream);
            });

            $disposition = HeaderUtils::makeDisposition(
                HeaderUtils::DISPOSITION_ATTACHMENT,
                $document->getFilename()
            );

            $response->headers->set('Content-Disposition', $disposition);
        } else {
            $response = new BinaryFileResponse($filepath);
        }

        return $response;
    }

    /**
     * Get document file content.
     */
    public function getFileContent(Document $document): string
    {
        $filepath = $this->getFilepath($document->getFilename());

        return file_get_contents($filepath);
    }

    public function getFilepath(string $filename): string
    {
        return $this->getFullDirectory().'/'.$filename;
    }

    /**
     * Move a file into an upload folder.
     *
     * @return string the filename of the uploaded path
     */
    public function uploadFile(string $filePath)
    {
        $filename = pathinfo($filePath, PATHINFO_FILENAME);
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);

        $safeFilename = $this->slugger->slug($filename);
        $newFilename = $safeFilename.'-'.uniqid().'.'.$extension;

        $targetPath = $this->getFullDirectory().'/'.$newFilename;
        $this->filesystem->rename($filePath, $targetPath);

        return basename($targetPath);
    }

    /**
     * Moves file to new place and overwrites if file already exists.
     *
     * @return string the filename of the uploaded path
     */
    public function replaceFile(string $filePath, string $filename)
    {
        $targetPath = $this->getFullDirectory().'/'.pathinfo($filename, PATHINFO_BASENAME);
        $this->filesystem->rename($filePath, $targetPath, true);

        return basename($targetPath);
    }

    public function getUploadDocumentDirectory(): string
    {
        return $this->uploadDocumentDirectory;
    }

    public function getProjectDirectory(): string
    {
        return $this->projectDirectory;
    }

    public function getFullDirectory(): string
    {
        return $this->projectDirectory.'/'.$this->uploadDocumentDirectory;
    }
}
