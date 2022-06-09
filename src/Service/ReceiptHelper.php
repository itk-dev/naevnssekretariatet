<?php

namespace App\Service;

use App\Entity\CaseDocumentRelation;
use App\Entity\CaseEntity;
use App\Entity\DigitalPost;
use App\Entity\HearingPost;
use Doctrine\Bundle\DoctrineBundle\EventSubscriber\EventSubscriberInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Symfony\Contracts\Translation\TranslatorInterface;

class ReceiptHelper implements EventSubscriberInterface
{
    public function __construct(private MailTemplateHelper $mailTemplateHelper, private DocumentUploader $documentUploader, private EntityManagerInterface $entityManager, private DigitalPostHelper $digitalPostHelper, private TranslatorInterface $translator)
    {
    }

    public function getSubscribedEvents()
    {
        return [
            Events::postPersist,
        ];
    }

    public function postPersist(LifecycleEventArgs $args): void
    {
        $entity = $args->getEntity();

        $case = null;
        $template = null;
        if ($entity instanceof CaseEntity) {
            $case = $entity;
            $digitalPostRecipients[] = (new DigitalPost\Recipient())
                ->setName($case->getBringer())
                ->setIdentifierType($case->getBringerIdentification()->getType())
                ->setIdentifier($case->getBringerIdentification()->getIdentifier())
                ->setAddress($case->getBringerAddress())
            ;
            $template = $case->getBoard()?->getReceiptCase();
            $documentTitle = $this->translator->trans('Case receipt', [], 'case');
            // @fixme What should this be? Can we get it from somewhere?
            $documentType = 'Case receipt';
        } elseif ($entity instanceof HearingPost) {
            $hearingPost = $entity;
            $digitalPostRecipients[] = (new DigitalPost\Recipient())
                ->setName($hearingPost->getRecipient()->getName())
                ->setIdentifierType($hearingPost->getRecipient()->getIdentification()->getType())
                ->setIdentifier($hearingPost->getRecipient()->getIdentification()->getIdentifier())
                ->setAddress($hearingPost->getRecipient()->getAddress())
            ;
            $case = $hearingPost->getHearing()?->getCaseEntity();
            $template = $case?->getBoard()?->getReceiptCase();
            $documentTitle = $this->translator->trans('Hearing post receipt', [], 'case');
            // @fixme What should this be? Can we get it from somewhere?
            $documentType = 'Hearing post receipt';
        }

        if (isset($case, $template)) {
            $fileName = $this->mailTemplateHelper->renderMailTemplate($template, $entity);
            $updatedFileName = $this->documentUploader->uploadFile($fileName);
            $user = $case->getAssignedTo();
            $document = $this->documentUploader->createDocumentFromPath($updatedFileName, $documentTitle, $documentType, $user);

            // Create case document relation
            $relation = (new CaseDocumentRelation())
                ->setCase($case)
                ->setDocument($document)
            ;

            $this->entityManager->persist($relation);
            $this->entityManager->persist($document);

            $this->digitalPostHelper->createDigitalPost($document, $documentTitle, get_class($entity), $entity->getId(), [], $digitalPostRecipients);
        }
    }
}
