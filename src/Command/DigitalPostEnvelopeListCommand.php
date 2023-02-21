<?php

namespace App\Command;

use App\Entity\DigitalPostEnvelope;
use App\Repository\DigitalPostEnvelopeRepository;
use Itkdev\BeskedfordelerBundle\Helper\MessageHelper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'tvist1:digital-post-envelope:list',
    description: 'List digital post envelopes',
)]
class DigitalPostEnvelopeListCommand extends Command
{
    public function __construct(private DigitalPostEnvelopeRepository $envelopeRepository, private MessageHelper $messageHelper)
    {
        parent::__construct(null);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $envelopes = $this->findEnvelopes($input);
        foreach ($envelopes as $envelope) {
            $data = array_map(
                fn (string $message) => $this->messageHelper->getBeskeddata($message),
                $envelope->getBeskedfordelerMessages()
            );

            $io->definitionList(
                ['Status' => $envelope->getStatus()],
                ['Status message' => $envelope->getStatusMessage()],
                ['Message Uuid' => $envelope->getMessageUuid()],
                ['Data' => json_encode($data, JSON_PRETTY_PRINT)],
            );
        }

        return self::SUCCESS;
    }

    /**
     * @return DigitalPostEnvelope[]
     */
    private function findEnvelopes(InputInterface $input): array
    {
        return $this->envelopeRepository->findAll();
    }
}
