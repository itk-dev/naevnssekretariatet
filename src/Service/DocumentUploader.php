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
    private $baseDocumentDirectory;
    private $documentDirectory = '';

    /**
     * @var Filesystem
     */
    private $filesystem;

    public function __construct(SluggerInterface $slugger, string $documentDirectory, Filesystem $filesystem)
    {
        $this->baseDocumentDirectory = rtrim($documentDirectory, '/');
        $this->filesystem = $filesystem;
        $this->slugger = $slugger;
    }

    /**
     * @throws DocumentDirectoryException
     */
    public function specifyDirectory(string $directory)
    {
        $this->documentDirectory = trim($directory, '/');
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
        return $this->baseDocumentDirectory.'/'.$this->documentDirectory;
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
        return $this->baseDocumentDirectory.'/'.$this->documentDirectory.'/'.$filename;
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

        $targetPath = $this->getDirectory().'/'.$newFilename;
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
        $targetPath = $this->getDirectory().'/'.pathinfo($filename, PATHINFO_BASENAME);
        $this->filesystem->rename($filePath, $targetPath, true);

        return basename($targetPath);
    }
}
