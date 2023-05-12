<?php

namespace App\Controller;

use App\Entity\CaseDocumentRelation;
use App\Entity\CaseEntity;
use App\Entity\Document;
use App\Entity\Hearing;
use App\Entity\HearingBriefing;
use App\Entity\HearingBriefingRecipient;
use App\Entity\HearingPost;
use App\Entity\HearingPostRequest;
use App\Exception\HearingException;
use App\Form\BriefingType;
use App\Repository\CaseDocumentRelationRepository;
use App\Service\DocumentUploader;
use App\Service\MailTemplateHelper;
use App\Service\PartyHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Contracts\Translation\TranslatorInterface;

class HearingBriefingController extends AbstractController
{
    public function __construct(private EntityManagerInterface $entityManager, private DocumentUploader $documentUploader, private CaseDocumentRelationRepository $relationRepository, private MailTemplateHelper $mailTemplateHelper, private TranslatorInterface $translator)
    {
    }

    /**
     * @Route("/{case}/hearing/{hearing}/request/{hearingPost}/briefing/create", name="case_hearing_briefing_create")
     */
    public function create(CaseEntity $case, Hearing $hearing, HearingPostRequest $hearingPost, PartyHelper $partyHelper, Request $request): Response
    {
        $this->denyAccessUnlessGranted('edit', $case);

        if ($hearing->getFinishedOn()) {
            throw new HearingException();
        }

        $availableParties = $partyHelper->getRelevantPartiesForHearingPostByCase($case);
        $mailTemplates = $this->mailTemplateHelper->getTemplates('briefing');

        $briefing = new HearingBriefing();

        $form = $this->createForm(BriefingType::class, $briefing, [
            'case_parties' => $availableParties,
            'mail_template_choices' => $mailTemplates,
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $customData = [];

            foreach ($form->get('customData') as $customField) {
                $name = $customField->getName();
                $customData[$name] = $customField->getData();
            }

            $briefing->setCustomData($customData);

            $hearingPost->setBriefing($briefing);
            $briefing->setHearingPostRequest($hearingPost);

            // Do something for each chosen recipient.
            $this->createBriefingRecipients($form->get('recipients')->getData()->toArray(), $briefing);

            // TODO: Skal alle udsendte høringsskrivelser medsendes?
            foreach ($hearingPost->getHearingRecipients() as $hearingRecipient) {
                $briefing->addAttachment($hearingRecipient->getDocument());
            }

            $this->entityManager->persist($briefing);
            $this->entityManager->flush();

            $this->addFlash('success', new TranslatableMessage('Hearing briefing created', [], 'case'));

            return $this->redirectToRoute('case_hearing_index', ['id' => $case->getId(), 'hearing' => $hearing->getId()]);
        }

        return $this->render('case/hearing/briefing/create.html.twig', [
            'translated_title' => $this->translator->trans('Create briefing', [], 'case'),
            'case' => $case,
            'hearing' => $hearing,
            'hearing_post_request' => $hearingPost,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{case}/hearing/{hearing}/request/{hearingPost}/briefing/{briefing}/edit", name="case_hearing_briefing_edit")
     */
    public function edit(CaseEntity $case, Hearing $hearing, HearingPostRequest $hearingPost, MailTemplateHelper $mailTemplateHelper, PartyHelper $partyHelper, HearingBriefing $briefing, Request $request): Response
    {
        $this->denyAccessUnlessGranted('edit', $case);

        if ($hearing->getFinishedOn()) {
            throw new HearingException();
        }

        $availableParties = $partyHelper->getRelevantPartiesForHearingPostByCase($case);
        $mailTemplates = $mailTemplateHelper->getTemplates('briefing');

        $form = $this->createForm(BriefingType::class, $briefing, [
            'case_parties' => $availableParties,
            'mail_template_choices' => $mailTemplates,
            'preselects' => array_map(static fn (HearingBriefingRecipient $briefingRecipient) => $briefingRecipient->getRecipient(), $briefing->getHearingBriefingRecipients()->toArray()),
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Regenerate recipients and their documents, i.e. remove then recreate.

            $customData = [];

            foreach ($form->get('customData') as $customField) {
                $name = $customField->getName();
                $customData[$name] = $customField->getData();
            }

            $briefing->setCustomData($customData);

            // Removal
            $briefingRecipients = $briefing->getHearingBriefingRecipients();
            foreach ($briefingRecipients as $briefingRecipient) {
                $document = $briefingRecipient->getDocument();

                $this->removeDocumentFromCase($case, $document);

                $this->entityManager->remove($briefingRecipient);
            }

            $this->entityManager->flush();

            // Regeneration
            $this->createBriefingRecipients($form->get('recipients')->getData(), $briefing);

            // TODO: Skal alle udsendte høringsskrivelser medsendes?
            foreach ($hearingPost->getHearingRecipients() as $hearingRecipient) {
                $briefing->addAttachment($hearingRecipient->getDocument());
            }

            $this->entityManager->persist($briefing);
            $this->entityManager->flush();

            $this->addFlash('success', new TranslatableMessage('Hearing briefing updated', [], 'case'));

            return $this->redirectToRoute('case_hearing_index', ['id' => $case->getId(), 'hearing' => $hearing->getId()]);
        }

        return $this->render('case/hearing/briefing/edit.html.twig', [
            'translated_title' => $this->translator->trans('Edit briefing', [], 'case'),
            'case' => $case,
            'hearing' => $hearing,
            'hearing_post_request' => $hearingPost,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{case}/hearing/{hearing}/request/{hearingPost}/briefing/{briefing}/show", name="case_hearing_briefing_show")
     */
    public function show(CaseEntity $case, Hearing $hearing, HearingPostRequest $hearingPost, HearingBriefing $briefing): Response
    {
        $this->denyAccessUnlessGranted('edit', $case);

        if ($hearing->getFinishedOn()) {
            throw new HearingException();
        }

        return $this->render('case/hearing/briefing/show.html.twig', [
            'translated_title' => $this->translator->trans('Create briefing', [], 'case'),
            'case' => $case,
            'briefing' => $briefing,
        ]);
    }

    /**
     * @Route("/{case}/hearing/{hearing}/request/{hearingPost}/briefing/cancel", name="case_hearing_briefing_cancel")
     */
    public function cancel(CaseEntity $case, Hearing $hearing, HearingPostRequest $hearingPost): Response
    {
        $hearingPost->setShouldSendBriefing(false);
        $this->entityManager->flush();

        return $this->redirectToRoute('case_hearing_index', ['id' => $case->getId()]);
    }

    public function removeDocumentFromCase(CaseEntity $case, Document $document)
    {
        $relation = $this->relationRepository->findOneBy(['case' => $case, 'document' => $document]);

        if (null !== $relation) {
            $this->entityManager->remove($relation);
            $this->entityManager->flush();
        }

        // Remove file
        $this->documentUploader->deleteDocumentFile($document);

        $this->entityManager->remove($document);
    }

    private function createBriefingRecipients(array $parties, HearingBriefing $briefing)
    {
        foreach ($parties as $recipient) {
            $briefingRecipient = new HearingBriefingRecipient();
            $briefingRecipient->setRecipient($recipient);
            $briefingRecipient->setHearingBriefing($briefing);

            // Create new file from template
            $fileName = $this->mailTemplateHelper->renderMailTemplate($briefing->getTemplate(), $briefingRecipient);

            // Create document
            $document = $this->documentUploader->createDocumentFromPath($fileName, $briefing->getTitle(), 'Hearing');

            $briefingRecipient->setDocument($document);

            // Create case document relation
            $relation = new CaseDocumentRelation();
            $relation->setCase($briefing->getHearingPostRequest()->getHearing()->getCaseEntity());
            $relation->setDocument($document);

            $this->entityManager->persist($relation);
            $this->entityManager->persist($document);
            $this->entityManager->persist($briefingRecipient);

            $briefing->addHearingBriefingRecipient($briefingRecipient);
        }

        $this->entityManager->flush();
    }
}
