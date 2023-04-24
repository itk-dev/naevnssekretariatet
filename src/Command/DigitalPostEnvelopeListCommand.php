<?php

namespace App\Command;

use App\Entity\DigitalPostAttachment;
use App\Entity\DigitalPostEnvelope;
use App\Repository\DigitalPostEnvelopeRepository;
use App\Service\DigitalPostEnvelopeHelper;
use DigitalPost\MeMo\Message;
use Doctrine\Common\Collections\Criteria;
use ItkDev\Serviceplatformen\Certificate\FilesystemCertificateLocator;
use ItkDev\Serviceplatformen\Service\SF1601\SF1601;
use ItkDev\Serviceplatformen\Service\SF1601\Serializer;
use Itkdev\BeskedfordelerBundle\Helper\MessageHelper;
use Oio\Fjernprint\ForsendelseI;
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
            ->addOption('digital-post-id', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Show only envelopes with the digital post id')
            ->addOption('digital-post-subject', null, InputOption::VALUE_REQUIRED, 'Show only envelopes with digital post subjects matching this LIKE expression')
            ->addOption('max-results', null, InputOption::VALUE_REQUIRED, 'Show at most this many envelopes', 10)
            ->addOption('id', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Envelope id')
            ->addOption('message-uuid', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Messaged uuid')
            ->addOption('forsendelse-uuid', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Show only digital posts with the forsendelses uuid')
            ->addOption('show-throwable', null, InputOption::VALUE_NONE, 'Show throwable')
            ->addOption('has-errors', null, InputOption::VALUE_NONE, 'Show envelopes with errors')
            ->addOption('show-errors', null, InputOption::VALUE_NONE, 'show errors')

            ->addOption('dump-kombi-request', null, InputOption::VALUE_NONE)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $showThrowable = $input->getOption('show-throwable');
        $showErrors = $input->getOption('show-errors');

        $envelopes = $this->findEnvelopes($input);
        foreach ($envelopes as $envelope) {
            if ($input->getOption('dump-kombi-request')) {
                return $this->dumpKombiRequest($envelope);
            }
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
        if ($forsendelsesUuids = $input->getOption('forsendelse-uuid')) {
            $forsendelsesUuids = array_map(static fn (string $id) => Uuid::fromString($id)->toRfc4122(), $forsendelsesUuids);
            $qb
                ->andWhere('e.forsendelseUuid IN (:forsendelsesUuids)')
                ->setParameter('forsendelsesUuids', $forsendelsesUuids)
            ;
        }
        if ($status = $input->getOption('status')) {
            $qb
                ->andWhere('e.status = :status')
                ->setParameter('status', $status)
            ;
        }
        if ($digitalPostIds = $input->getOption('digital-post-id')) {
            $digitalPostIds = array_map(static fn (string $id) => Uuid::fromString($id)->toBinary(), $digitalPostIds);
            $qb
                ->join('e.digitalPost', 'p_id')
                ->andWhere('p_id.id IN (:digitalPostIds)')
                ->setParameter('digitalPostIds', $digitalPostIds)
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

    private function dumpKombiRequest(DigitalPostEnvelope $envelope)
    {
        $serializer = new Serializer();
        $method = new \ReflectionMethod($serializer, 'serializer');
        /** @var \JMS\Serializer\SerializerInterface $serializer */
        $serializer = $method->invoke($serializer);
        $message = $serializer->deserialize($envelope->getMeMoMessage(), Message::class, 'xml');
        $forsendelse = $serializer->deserialize($envelope->getForsendelse(), ForsendelseI::class, 'xml');
        $sf1601 = new SF1601(['authority_cvr' => null, 'certificate_locator' => new FilesystemCertificateLocator(__FILE__)]);
        $method = new \ReflectionMethod($sf1601, 'buildKombiRequestDocument');
        $type = SF1601::TYPE_AUTOMATISK_VALG;
        /** @var \DOMDocument $result */
        $result = $method->invoke($sf1601, $type, $message, $forsendelse);
        $result->formatOutput = true;

        echo $result->saveXML();

        return self::SUCCESS;
    }
}
