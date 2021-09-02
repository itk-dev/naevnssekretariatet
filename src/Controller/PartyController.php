<?php

namespace App\Controller;

use App\Entity\CaseEntity;
use App\Entity\CasePartyRelation;
use App\Entity\Party;
use App\Form\AddPartyFromIndexType;
use App\Form\PartyFormType;
use App\Repository\CasePartyRelationRepository;
use App\Repository\PartyRepository;
use App\Service\PartyHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/case/{id}/party")
 */
class PartyController extends AbstractController
{
    /**
     * @var PartyHelper
     */
    private $partyHelper;

    public function __construct(PartyHelper $partyHelper)
    {
        $this->partyHelper = $partyHelper;
    }

    /**
     * @Route("/add", name="party_add")
     */
    public function addParty(CaseEntity $case, Request $request): Response
    {
        $party = new Party();

        $form = $this->createForm(PartyFormType::class, null, [
            'party_action' => 'Add',
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $this->partyHelper->handleAddPartyForm($case, $party, $data);

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
     * @Route("/add_party_from_index", name="party_add_from_index")
     */
    public function addPartyFromIndex(CaseEntity $case, CasePartyRelationRepository $relationRepository, PartyRepository $partyRepository, Request $request): Response
    {
        $party = null;

        // Make sure we only get the option of adding parties that are not already added
        $partyChoices = $this->partyHelper->findPartyIndexChoices($case);

        $form = $this->createForm(AddPartyFromIndexType::class, $party, [
            'party_choices' => $partyChoices,
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $this->partyHelper->handleAddPartyFromIndex($case, $data);

            return $this->redirectToRoute('case_show', ['id' => $case->getId()]);
        }

        return $this->render('party/add_party_from_index.html.twig', [
            'case' => $case,
            'add_party_form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/edit/{party_id}", name="party_edit")
     * @Entity("party", expr="repository.find(party_id)")
     * @Entity("case", expr="repository.find(id)")
     */
    public function edit(CaseEntity $case, Party $party, CasePartyRelationRepository $relationRepository, Request $request): Response
    {
        $form = $this->createForm(PartyFormType::class, null, [
            'party_action' => 'Edit',
        ]);

        /** @var CasePartyRelation $relation */
        $relation = $relationRepository->findOneBy(['case' => $case, 'party' => $party]);

        $form = $this->partyHelper->setUpFormData($party, $relation, $form);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $this->partyHelper->handleEditParty($party, $relation, $data);

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

    /**
     * @Route("/delete/{party_id}", name="party_delete", methods={"DELETE"})
     * @Entity("party", expr="repository.find(party_id)")
     * @Entity("case", expr="repository.find(id)")
     */
    public function delete(Request $request, Party $party, CaseEntity $case, CasePartyRelationRepository $relationRepository): Response
    {
        // Check that CSRF token is valid
        if ($this->isCsrfTokenValid('delete'.$party->getId(), $request->request->get('_token'))) {
            // Simply just soft delete by setting soft deleted to true

            $this->partyHelper->handleDeleteParty($case, $party);
        }

        return $this->redirectToRoute('case_show', ['id' => $case->getId()]);
    }
}
