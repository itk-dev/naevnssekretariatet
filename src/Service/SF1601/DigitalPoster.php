<?php

namespace App\Service\SF1601;

use App\Entity\DigitalPost;
use App\Entity\DigitalPostEnvelope;
use App\Repository\DigitalPostEnvelopeRepository;
use ItkDev\Serviceplatformen\Service\SF1601\Serializer;
use ItkDev\Serviceplatformen\Service\SF1601\SF1601;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;

class DigitalPoster
{
    use LoggerAwareTrait;

    private array $options;

    public function __construct(private CertificateLocatorHelper $certificateLocatorHelper, private MeMoHelper $meMoHelper, private readonly ForsendelseHelper $forsendelseHelper, private DigitalPostEnvelopeRepository $envelopeRepository, LoggerInterface $logger, array $options)
    {
        $this->options = $this->resolveOptions($options);
        $this->setLogger($logger);
    }

    public function canReceive(string $type, string $identifier): ?bool
    {
        $cache = new FilesystemAdapter();
        $cacheKey = preg_replace(
            '#[{}()/\\\\@:]+#',
            '_',
            implode('|||', [__METHOD__, $type, $identifier])
        );

        return $cache->get($cacheKey, function (ItemInterface $item) use ($type, $identifier): ?bool {
            try {
                $service = $this->getSF1601();
                $transactionId = Serializer::createUuid();
                $result = $service->postForespoerg($transactionId, $type, $identifier);
                $item->expiresAt(new \DateTimeImmutable($this->options['post_forespoerg_cache_expire_at']));
            } catch (\Throwable $throwable) {
                // Never cache is case of error.
                $item->expiresAt(new \DateTimeImmutable('2001-01-01'));
            }

            return true === ($result['result'] ?? false);
        });
    }

    public function sendDigitalPost(DigitalPost $digitalPost, DigitalPost\Recipient $recipient)
    {
        $isNew = false;
        $envelope = $this->envelopeRepository->findOneBy([
            'digitalPost' => $digitalPost,
            'recipient' => $recipient,
        ]);
        if (null === $envelope) {
            $isNew = true;
            $this->logger->debug(sprintf(
                'Creating new envelope for digital post %s for sending to %s',
                $digitalPost,
                $recipient
            ));
            $envelope = (new DigitalPostEnvelope())
                ->setDigitalPost($digitalPost)
                ->setRecipient($recipient)
            ;
        } else {
            $this->logger->debug(sprintf(
                'Reusing envelope %s for digital post %s for sending to %s',
                $envelope->getMeMoMessageUuid(),
                $digitalPost,
                $recipient
            ));
        }

        try {
            $meMoOptions = [
                MeMoHelper::SENDER_IDENTIFIER_TYPE => MeMoHelper::IDENTIFIER_TYPE_CVR,
                MeMoHelper::SENDER_IDENTIFIER => $this->options['sf1601']['authority_cvr'],
                MeMoHelper::SENDER_LABEL => $this->options['sf1601']['sender_label'],
            ];

            // For performance reasons we want send as little a payload as possible. Using SF1601::TYPE_AUTOMATISK_VALG
            // will require us to duplicate the content in the call to "kombiPostAfsend".
            $meMoMessage = null;
            $forsendelse = null;
            $type = $this->canReceive(SF1601::FORESPOERG_TYPE_DIGITAL_POST, $recipient->getIdentifier() ?? '') ? SF1601::TYPE_DIGITAL_POST : SF1601::TYPE_FYSISK_POST;

            if (in_array($type, [SF1601::TYPE_DIGITAL_POST, SF1601::TYPE_AUTOMATISK_VALG], true)) {
                $meMoMessage = $this->meMoHelper->createMeMoMessage($digitalPost, $recipient, $meMoOptions);
            }

            if (in_array($type, [SF1601::TYPE_FYSISK_POST, SF1601::TYPE_AUTOMATISK_VALG], true)) {
                $forsendelseOptions = [
                    ForsendelseHelper::FORSENDELSES_TYPE_IDENTIFIKATOR => $this->options['sf1601']['forsendelses_type_identifikator'],
                ];
                $forsendelse = $this->forsendelseHelper->createForsendelse($digitalPost, $recipient, $forsendelseOptions);
            }

            $service = $this->getSF1601();
            $transactionId = Serializer::createUuid();
            $response = $service->kombiPostAfsend($transactionId, $type, $meMoMessage, $forsendelse);

            $serializer = new Serializer();
            $receipt = $response->getContent();

            // We don't want to store actual document content in the envelope.
            $this->meMoHelper->removeDocumentContent($meMoMessage);

            $envelope
                ->setTransactionId(Uuid::fromRfc4122($transactionId))
                ->setStatus(DigitalPostEnvelope::STATUS_SENT)
                ->setStatusMessage(null)
                ->setReceipt($receipt)
            ;

            if (null !== $meMoMessage) {
                $envelope
                    ->setMeMoMessage($serializer->serialize($meMoMessage))
                    ->setMeMoMessageUuid($meMoMessage->getMessageHeader()->getMessageUUID())
                ;
            }

            if (null !== $forsendelse) {
                $this->forsendelseHelper->removeDocumentContent($forsendelse);
                $envelope
                    ->setForsendelse($serializer->serialize($forsendelse))
                    ->setForsendelseUuid($forsendelse->getAfsendelseIdentifikator())
                ;
            }

            $this->envelopeRepository->save($envelope, true);

            $this->logger->info(
                $isNew
                    ? sprintf(
                        'Created envelope %s for digital post %s to %s',
                        $envelope->getMeMoMessageUuid(),
                        $digitalPost,
                        $recipient
                    )
                    : sprintf(
                        'Reused envelope %s for digital post %s to %s',
                        $envelope->getMeMoMessageUuid(),
                        $digitalPost,
                        $recipient
                    )
            );
        } catch (\Throwable $throwable) {
            $context = [];
            if ($throwable instanceof ClientExceptionInterface) {
                $response = $throwable->getResponse();
                $context['response'] = [
                    'headers' => $response->getHeaders(false),
                    'content' => $response->getContent(false),
                ];
            }
            $message = sprintf('Error sending digital post: %s', $throwable->getMessage());
            $this->logger->error($message, $context);

            $envelope
                ->addError($message, $context)
                ->setStatus(DigitalPostEnvelope::STATUS_FAILED)
                ->setThrowable($throwable)
            ;

            $this->envelopeRepository->save($envelope, true);

            // Rethrow exception for proper messenger bus retrying.
            throw $throwable;
        }
    }

    /**
     * Get SF1601 instance.
     */
    private function getSF1601(): SF1601
    {
        $options = $this->options['sf1601']
        + [
            'certificate_locator' => $this->certificateLocatorHelper->getCertificateLocator(),
        ];
        unset($options[ForsendelseHelper::FORSENDELSES_TYPE_IDENTIFIKATOR]);
        unset($options[MeMoHelper::SENDER_LABEL]);

        return new SF1601($options);
    }

    private function resolveOptions(array $options): array
    {
        return (new OptionsResolver())
            ->setDefault('sf1601', static function (OptionsResolver $resolver) {
                $resolver
                    ->setDefault('test_mode', true)
                    ->setAllowedTypes('test_mode', 'bool')
                    ->setRequired('authority_cvr')
                    ->setAllowedTypes('authority_cvr', 'string')
                    ->setRequired('forsendelses_type_identifikator')
                    ->setAllowedTypes('forsendelses_type_identifikator', 'int')
                    ->setRequired('sender_label')
                    ->setAllowedTypes('sender_label', 'string')
                ;
            })
            ->setDefault('post_forespoerg_cache_expire_at', '+1 day')
            ->resolve($options)
        ;
    }
}
