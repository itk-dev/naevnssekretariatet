<?php

namespace App\Service;

use App\Entity\Agenda;
use App\Entity\CaseDocumentRelation;
use App\Entity\DigitalPostEnvelope;
use App\Repository\AgendaRepository;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class DigitalPostEnvelopeHelper
{
    public function __construct(
        readonly private UrlGeneratorInterface $urlGenerator,
        readonly private AgendaRepository $agendaRepository
    ) {
    }

    public function getDigitalPostUrls(DigitalPostEnvelope $envelope): array
    {
        $digitalPost = $envelope->getDigitalPost();

        return array_merge(
            array_map(
                fn (CaseDocumentRelation $relation) => $this->urlGenerator->generate('digital_post_show', ['id' => $relation->getCase()->getId(), 'digitalPost' => $digitalPost->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
                $digitalPost->getDocument()->getCaseDocumentRelations()->toArray()
            ),
            array_map(
                fn (Agenda $agenda) => $this->urlGenerator->generate('agenda_broadcast_show', ['id' => $agenda->getId(), 'digital_post' => $digitalPost->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
                Agenda::class === $digitalPost->getEntityType()
                    ? $this->agendaRepository->findBy(['id' => $digitalPost->getEntityId()->toBinary()])
                    : []
            )
        );
    }
}
