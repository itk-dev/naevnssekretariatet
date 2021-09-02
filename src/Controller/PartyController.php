<?php

namespace App\Controller;

use App\Entity\CaseEntity;
use App\Entity\CasePartyRelation;
use App\Entity\Party;
use App\Form\AddPartyFromIndexType;
use App\Form\PartyFormType;
use App\Repository\CasePartyRelationRepository;
use App\Repository\PartyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/case/{id}/party")
 */
class PartyController extends AbstractController
{

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManger)
    {
        $this->entityManager = $entityManger;
    }

    /**
     * @Route("/add", name="case_add_party")
     */

    public function addParty(CaseEntity $case, Request $request): Response
    {
        $party = new Party();

        $form = $this->createForm(PartyFormType::class, null , [
            'party_action' => 'Add'
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $this->handleAddPartyForm($case, $party, $data);

            return $this->redirectToRoute('case_show', [
                'id' => $case->getId(),
            ]);
        }

        return $this->render('party/add.html.twig', [
            'case' => $case,
            'add_party_form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/add_party_from_index", name="case_add_party_from_index")
     */
    public function addPartyFromIndex(CaseEntity $case, Request $request, PartyRepository $partyRepository): Response
    {
        $party = null;

        $form = $this->createForm(AddPartyFromIndexType::class, $party, [
            'party_repository' => $partyRepository,
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Party $party */
            $party = $form->get('partyToAdd')->getData();

            $relation = new CasePartyRelation();
            $relation->setCase($case);
            $relation->setParty($party);
            $relation->setType($form->get('type')->getData());

            $this->entityManager->persist($relation);
            $this->entityManager->flush();

            return $this->redirectToRoute('case_show', ['id' => $case->getId()]);
        }

        return $this->render('party/add_party_from_index.html.twig', [
            'case' => $case,
            'add_party_form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/edit/{party_id}", name="case_party_edit")
     * @Entity("party", expr="repository.find(party_id)")
     * @Entity("case", expr="repository.find(id)")
     */
    public function edit(CaseEntity $case, Party $party, CasePartyRelationRepository $relationRepository, Request $request): Response
    {
        $form = $this->createForm(PartyFormType::class, null, [
            'party_action' => 'Edit',
        ]);

        /** @var CasePartyRelation $relation */
        $relation = $relationRepository->findOneBy(['case' => $case,'party' => $party]);

        $form = $this->setUpFormData($party, $relation, $form);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $data = $form->getData();

            $party->setName($data['name']);
            $party->setCpr($data['cpr']);
            $party->setAddress($data['address']);
            $party->setPhoneNumber($data['phoneNumber']);
            $party->setJournalNumber($data['journalNumber']);
            $relation->setType($data['type']);

            $this->entityManager->flush();

            return $this->redirectToRoute('case_show', [
                'id' => $case->getId(),
                'case' => $case,
            ]);
        }

        return $this->render('party/edit.html.twig', [
            'case' => $case,
            'edit_party_form' => $form->createView(),
        ]);
    }


    private function handleAddPartyForm(CaseEntity $case, Party $party, $data)
    {
        $party->setName($data['name']);
        $party->setCpr($data['cpr']);
        $party->setAddress($data['address']);
        $party->setPhoneNumber($data['phoneNumber']);
        $party->setJournalNumber($data['journalNumber']);

        // Do not add to part index from here
        $party->setIsPartOfPartIndex(false);

        $this->entityManager->persist($party);

        $relation = new CasePartyRelation();
        $relation->setCase($case);
        $relation->setParty($party);
        $relation->setType($data['type']);

        $this->entityManager->persist($relation);

        $this->entityManager->flush();
    }

    private function setUpFormData(Party $party, CasePartyRelation $relation, FormInterface $form): FormInterface
    {
        $form->get('name')->setData($party->getName());
        $form->get('cpr')->setData($party->getCpr());
        $form->get('address')->setData($party->getAddress());
        $form->get('phoneNumber')->setData($party->getPhoneNumber());
        $form->get('journalNumber')->setData($party->getJournalNumber());
        $form->get('type')->setData($relation->getType());

        return $form;
    }
}
