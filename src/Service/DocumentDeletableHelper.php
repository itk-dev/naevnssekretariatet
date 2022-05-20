<?php

namespace App\Service;

use App\Entity\CaseEntity;
use App\Entity\Document;
use App\Repository\AgendaCaseItemRepository;
use App\Repository\DigitalPostAttachmentRepository;
use App\Repository\DigitalPostRepository;

class DocumentDeletableHelper
{
    public function __construct(private DigitalPostRepository $digitalPostRepository, private DigitalPostAttachmentRepository $digitalPostAttachmentRepository, private AgendaCaseItemRepository $agendaCaseItemRepository)
    {
    }

    /**
     * Checks whether document on case is deletable.
     */
    public function isDocumentDeletable(Document $document, CaseEntity $case): bool
    {
        if (count($this->digitalPostRepository->findByDocumentAndCase($document, $case)) > 0) {
            return false;
        }

        if (count($this->digitalPostAttachmentRepository->findByDocumentAndCase($document, $case)) > 0) {
            return false;
        }

        if (count($this->agendaCaseItemRepository->findByDocumentAndCase($document, $case)) > 0) {
            return false;
        }

        return true;
    }
}
