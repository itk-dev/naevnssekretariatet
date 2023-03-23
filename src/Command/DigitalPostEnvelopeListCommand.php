<?php

namespace App\Command;

use App\Entity\CaseDocumentRelation;
use App\Entity\DigitalPostEnvelope;
use App\Repository\DigitalPostEnvelopeRepository;
use Doctrine\Common\Collections\Criteria;
use Itkdev\BeskedfordelerBundle\Helper\MessageHelper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Yaml\Yaml;

#[AsCommand(
    name: 'tvist1:digital-post-envelope:list',
    description: 'List digital post envelopes',
)]
class DigitalPostEnvelopeListCommand extends Command
{
    public function __construct(readonly private DigitalPostEnvelopeRepository $envelopeRepository, readonly private MessageHelper $messageHelper, readonly private UrlGeneratorInterface $urlGenerator)
    {
        parent::__construct(null);
    }

    protected function configure()
    {
        $this
            ->addOption('status', null, InputOption::VALUE_REQUIRED, 'Show only envelopes with this status')
            ->addOption('digital-post-subject', null, InputOption::VALUE_REQUIRED, 'Show only envelopes with subject matching this LIKE expression')
        ;
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

            $digitalPost = $envelope->getDigitalPost();
            $digitalPostUrls = array_map(
                fn (CaseDocumentRelation $relation) => $this->urlGenerator->generate('digital_post_show', ['id' => $relation->getCase()->getId(), 'digitalPost' => $digitalPost->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
                $digitalPost->getDocument()->getCaseDocumentRelations()->toArray()
            );
            $io->definitionList(
                ['Status' => $envelope->getStatus()],
                ['Status message' => $envelope->getStatusMessage()],
                ['MeMo message uuid' => $envelope->getMeMoMessageUuid()],
                ['Forsendelse uuid' => $envelope->getForsendelseUuid()],
                ['Created at' => $envelope->getCreatedAt()->format(\DateTimeInterface::ATOM)],
                ['Updated at' => $envelope->getUpdatedAt()->format(\DateTimeInterface::ATOM)],
                ['Beskedfordeler message data' => Yaml::dump($data, PHP_INT_MAX)],
                ['Digital post' => (string) $digitalPost],
                ['Digital post URL' => implode(PHP_EOL, $digitalPostUrls)]
            );
        }

        return self::SUCCESS;
    }

    /**
     * @return DigitalPostEnvelope[]
     */
    private function findEnvelopes(InputInterface $input): array
    {
        $criteria = array_filter([
            'status' => $input->getOption('status'),
        ]);

        $qb = $this->envelopeRepository
            ->createQueryBuilder('e')
            ->orderBy('e.createdAt', Criteria::DESC)
        ;

        if ($status = $input->getOption('status')) {
            $qb
                ->andWhere('e.status = :status')
                ->setParameter(':status', $status)
            ;
        }
        if ($subject = $input->getOption('digital-post-subject')) {
            $qb
                ->join('e.digitalPost', 'p')
                ->andWhere('p.subject LIKE :subject')
                ->setParameter(':subject', $subject)
            ;
        }

        return $qb->getQuery()->getResult();
    }
}
