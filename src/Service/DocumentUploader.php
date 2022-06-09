<?php

namespace App\Service;

use App\Entity\Document;
use App\Entity\User;
use App\Exception\FileMovingException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Mime\MimeTypeGuesserInterface;
use Symfony\Component\Security\Core\Security;
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
    private MimeTypeGuesserInterface $mimeTypeGuesser;
    private Security $security;

    public function __construct(SluggerInterface $slugger, string $uploadDocumentDirectory, string $projectDirectory, Filesystem $filesystem, MimeTypeGuesserInterface $mimeTypeGuesser, Security $security)
    {
        $this->projectDirectory = $projectDirectory;
        $this->uploadDocumentDirectory = $uploadDocumentDirectory;
        $this->filesystem = $filesystem;
        $this->slugger = $slugger;
        $this->mimeTypeGuesser = $mimeTypeGuesser;
        $this->security = $security;
    }

    /**
     * Creates and returns new document from filename.
     */
    public function createDocumentFromPath(string $fileName, string $documentName, string $documentType, User $user = null): Document
    {
        $document = new Document();

        $document->setFilename($fileName);
        $document->setDocumentName($documentName);
        $document->setPath($this->getFilepathFromProjectDirectory($fileName));
        $document->setUploadedBy($user ?? $this->security->getUser());
        $document->setType($documentType);

        return $document;
    }

    /**
     * Creates, uploads and returns new document from a file.
     */
    public function createDocumentFromUploadedFile(UploadedFile $file, string $documentName, string $documentType): Document
    {
        $newFileName = $this->upload($file);

        $document = $this->createDocumentFromPath($newFileName, $documentName, $documentType);

        // Documents that are created via an UploadedFile has an original file name others (template generated) do not
        $document->setOriginalFileName($file->getClientOriginalName());

        return $document;
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
     * Handles view document.
     */
    public function handleViewDocument(Document $document, bool $forceDownload = false): Response
    {
        $filepath = $this->getFilepath($document->getFilename());

        // @see https://symfonycasts.com/screencast/symfony-uploads/file-streaming
        $response = new StreamedResponse(function () use ($filepath) {
            $outputStream = fopen('php://output', 'wb');
            $fileStream = fopen($filepath, 'r');
            stream_copy_to_stream($fileStream, $outputStream);
        });

        if ($forceDownload) {
            $disposition = HeaderUtils::makeDisposition(
                HeaderUtils::DISPOSITION_ATTACHMENT,
                $document->getFilename()
            );

            $response->headers->set('Content-Disposition', $disposition);
        } else {
            $response->headers->set('Content-Type', $this->mimeTypeGuesser->guessMimeType($this->getFilepath($document->getFilename())));
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

    public function getFilepathFromProjectDirectory(string $filename): string
    {
        return $this->uploadDocumentDirectory.'/'.$filename;
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

    public function getFullDirectory(): string
    {
        return $this->projectDirectory.'/'.$this->uploadDocumentDirectory;
    }
}
