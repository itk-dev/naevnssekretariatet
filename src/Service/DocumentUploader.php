<?php

namespace App\Service;

use App\Entity\CaseEntity;
use App\Entity\Document;
use App\Entity\MailTemplate;
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
    private $baseDocumentDirectory;
    private $documentDirectory = '';

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var MailTemplateHelper
     */
    private $mailTemplateHelper;

    public function __construct(SluggerInterface $slugger, string $documentDirectory, Filesystem $filesystem, MailTemplateHelper $mailTemplateHelper)
    {
        $this->baseDocumentDirectory = rtrim($documentDirectory, '/');
        $this->filesystem = $filesystem;
        $this->slugger = $slugger;
        $this->mailTemplateHelper = $mailTemplateHelper;
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
        $response = new BinaryFileResponse($filepath);
        if ($forceDownload) {
            $disposition = HeaderUtils::makeDisposition(
                HeaderUtils::DISPOSITION_ATTACHMENT,
                $document->getFilename()
            );

            $response->headers->set('Content-Disposition', $disposition);
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

    private function getFilepath(string $filename): string
    {
        return $this->baseDocumentDirectory.'/'.$this->documentDirectory.'/'.$filename;
    }

    public function uploadFileFromTemplate(MailTemplate $mailTemplate, CaseEntity $case): string
    {
        // Create new file from template
        $fileName = $this->mailTemplateHelper->renderMailTemplate($mailTemplate, $case);

        // Compute fitting name
        $updatedFileName = $this->slugger->slug($mailTemplate->getName()).'-'.uniqid().'.pdf';

        // Move into correct directory and rename
        $this->filesystem->rename($fileName, $this->getDirectory().'/'.$updatedFileName, true);

        return $updatedFileName;
    }
}
