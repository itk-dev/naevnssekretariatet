<?php

namespace App\Command;

use App\Entity\DigitalPostAttachment;
use App\Entity\DigitalPostEnvelope;
use App\Repository\AgendaRepository;
use App\Repository\DigitalPostEnvelopeRepository;
use App\Service\DigitalPostEnvelopeHelper;
use Doctrine\Common\Collections\Criteria;
use Itkdev\BeskedfordelerBundle\Helper\MessageHelper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Yaml\Yaml;

#[AsCommand(
    name: 'tvist1:digital-post-envelope:list',
    description: 'List digital post envelopes',
)]
class DigitalPostEnvelopeListCommand extends Command
{
    public function __construct(
        readonly private DigitalPostEnvelopeRepository $envelopeRepository,
        readonly private DigitalPostEnvelopeHelper $envelopeHelper,
        readonly private MessageHelper $messageHelper,
        readonly private UrlGeneratorInterface $urlGenerator
    ) {
        parent::__construct(null);
    }

    protected function configure()
    {
        $this
            ->addOption('status', null, InputOption::VALUE_REQUIRED, 'Show only envelopes with this status')
            ->addOption('digital-post-subject', null, InputOption::VALUE_REQUIRED, 'Show only envelopes with subject matching this LIKE expression')
            ->addOption('max-results', null, InputOption::VALUE_REQUIRED, 'Show at most this many envelopes', 10)
            ->addOption('id', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Envelope id')
            ->addOption('message-uuid', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Messaged uuid')
            ->addOption('show-throwable', null, InputOption::VALUE_NONE, 'show throwable')
            ->addOption('has-errors', null, InputOption::VALUE_NONE, 'Show envelopes with errors')
            ->addOption('show-errors', null, InputOption::VALUE_NONE, 'show errors')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $showThrowable = $input->getOption('show-throwable');
        $showErrors = $input->getOption('show-errors');

        $envelopes = $this->findEnvelopes($input);
        foreach ($envelopes as $envelope) {
            $data = array_map(
                fn (string $message) => $this->messageHelper->getBeskeddata($message),
                $envelope->getBeskedfordelerMessages()
            );

            $digitalPostUrls = $this->envelopeHelper->getDigitalPostUrls($envelope);

            $digitalPost = $envelope->getDigitalPost();
            $filenames = array_merge(
                [$digitalPost->getDocument()->getOriginalFileName()],
                array_map(
                    static fn (DigitalPostAttachment $attachment) => $attachment->getDocument()->getOriginalFileName(),
                    $digitalPost->getAttachments()->toArray()
                )
            );

            $items = [
                ['Id' => $envelope->getId()],
                ['Status' => $envelope->getStatus()],
                ['Status message' => $envelope->getStatusMessage()],
                ['MeMo message uuid' => $envelope->getMeMoMessageUuid()],
                ['Forsendelse uuid' => $envelope->getForsendelseUuid()],
                ['Created at' => $envelope->getCreatedAt()->format(\DateTimeInterface::ATOM)],
                ['Updated at' => $envelope->getUpdatedAt()->format(\DateTimeInterface::ATOM)],
                ['Beskedfordeler message data' => Yaml::dump($data, PHP_INT_MAX)],
                ['Digital post' => (string) $digitalPost],
                ['Filenames' => implode(PHP_EOL, $filenames)],
                ['Digital post URL' => implode(PHP_EOL, $digitalPostUrls)],
                ['Errors' => count($envelope->getErrors())],
            ];

            if ($showThrowable) {
                $throwable = unserialize($envelope->getThrowable()) ?: null;
                $items[] = ['Throwable' => var_export($throwable, true)];
            }

            if ($showErrors) {
                $items[] = ['Errors' => var_export($envelope->getErrors(), true)];
            }

            $io->definitionList(...$items);
        }

        return self::SUCCESS;
    }

    /**
     * @return DigitalPostEnvelope[]
     */
    private function findEnvelopes(InputInterface $input): array
    {
        $maxResults = (int) $input->getOption('max-results');
        $qb = $this->envelopeRepository
            ->createQueryBuilder('e')
            ->orderBy('e.createdAt', Criteria::DESC)
            ->setMaxResults($maxResults)
        ;

        if ($ids = $input->getOption('id')) {
            $ids = array_map(static fn (string $id) => Uuid::fromString($id)->toBinary(), $ids);
            $qb
                ->andWhere('e.id IN (:ids)')
                ->setParameter('ids', $ids)
            ;
        }
        if ($messageUuids = $input->getOption('message-uuid')) {
            $messageUuids = array_map(static fn (string $id) => Uuid::fromString($id)->toRfc4122(), $messageUuids);
            $qb
                ->andWhere('e.meMoMessageUuid IN (:messageUuids)')
                ->setParameter('messageUuids', $messageUuids)
            ;
        }
        if ($status = $input->getOption('status')) {
            $qb
                ->andWhere('e.status = :status')
                ->setParameter('status', $status)
            ;
        }
        if ($subject = $input->getOption('digital-post-subject')) {
            $qb
                ->join('e.digitalPost', 'p')
                ->andWhere('p.subject LIKE :subject')
                ->setParameter('subject', $subject)
            ;
        }

        if ($input->getOption('has-errors')) {
            $qb
                ->andWhere('e.errors <> \'[]\'')
            ;
        }

        return $qb->getQuery()->getResult();
    }
}
