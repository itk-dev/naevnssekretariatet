<?php

namespace App\Controller;

use App\Entity\CaseEntity;
use App\Entity\CasePartyRelation;
use App\Entity\Party;
use App\Form\PartyType;
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
     * @Route("/add", name="case_add_party")
     */

    public function addParty(CaseEntity $case, Request $request): Response
    {
        $party = new Party();

        $form = $this->createForm(PartyType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Party $party */
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

    private function handleAddPartyForm(CaseEntity $case, Party $party, $data)
    {
        $party->setName($data['name']);
        $party->setCpr($data['cpr']);
        $party->setAddress($data['address']);
        $party->setPhoneNumber($data['phoneNumber']);
        $party->setJournalNumber($data['journalNumber']);

        // Do not add to part index from here
        $party->setIsPartOfPartIndex(false);

        $em = $this->getDoctrine()->getManager();
        $em->persist($party);

        $relation = new CasePartyRelation();
        $relation->setCase($case);
        $relation->setParty($party);
        $relation->setType($data['type']);

        $em->persist($relation);
        //$case->addParty($party);

        $em->flush();
    }
}
