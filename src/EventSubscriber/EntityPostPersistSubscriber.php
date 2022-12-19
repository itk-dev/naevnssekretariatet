<?php

namespace App\EventSubscriber;

use App\Entity\CaseDocumentRelation;
use App\Entity\CaseEntity;
use App\Entity\DigitalPost;
use App\Entity\HearingPostResponse;
use App\Service\DigitalPostHelper;
use App\Service\DocumentUploader;
use App\Service\MailTemplateHelper;
use Doctrine\Bundle\DoctrineBundle\EventSubscriber\EventSubscriberInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Symfony\Contracts\Translation\TranslatorInterface;

class EntityPostPersistSubscriber implements EventSubscriberInterface
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
        // @todo: Reintroduce this at a later stage.
        if ($entity instanceof CaseEntity && false) {
            $case = $entity;
            $digitalPostRecipients[] = (new DigitalPost\Recipient())
                ->setName($case->getBringer())
                ->setIdentifierType($case->getBringerIdentification()->getType())
                ->setIdentifier($case->getBringerIdentification()->getIdentifier())
                ->setAddress($case->getBringerAddress())
            ;
            $template = $case->getBoard()?->getReceiptCase();
            $documentTitle = $this->translator->trans('Case receipt', [], 'case');
            // The document type is translated in templates/translations/mail_template.html.twig
            $documentType = 'Case created receipt';
        } elseif ($entity instanceof HearingPostResponse && $entity->getSendReceipt()) {
            $hearingPostResponse = $entity;
            $sender = $hearingPostResponse->getSender();
            $digitalPostRecipients[] = (new DigitalPost\Recipient())
                ->setName($sender->getName())
                ->setIdentifierType($sender->getIdentification()->getType())
                ->setIdentifier($sender->getIdentification()->getIdentifier())
                ->setAddress($sender->getAddress())
            ;
            $case = $hearingPostResponse->getHearing()?->getCaseEntity();
            $template = $case?->getBoard()?->getReceiptHearingPost();
            $documentTitle = $this->translator->trans('Hearing post response receipt', [], 'case');
            // The document type is translated in templates/translations/mail_template.html.twig
            $documentType = 'Hearing post response created receipt';

            // Use this listener to modify hearing response properties on case,
            // as it already detects creation of hearing post responses via OS2Forms and TVIST1.
            $case->setHearingResponseDeadline(null);
            $case->setHasReachedHearingResponseDeadline(false);
        }

        if (isset($case, $template)) {
            $fileName = $this->mailTemplateHelper->renderMailTemplate($template, $entity);
            $user = $case->getAssignedTo();
            $document = $this->documentUploader->createDocumentFromPath($fileName, $documentTitle, $documentType, $user);

            // Create case document relation
            $relation = (new CaseDocumentRelation())
                ->setCase($case)
                ->setDocument($document)
            ;

            $this->entityManager->persist($relation);
            $this->entityManager->persist($document);

            $this->digitalPostHelper->createDigitalPost($document, $documentTitle, get_class($case), $case->getId(), [], $digitalPostRecipients);
        }
    }
}
