<?php

namespace App\Service\SF1601;

use App\Entity\DigitalPost;
use App\Entity\DigitalPostEnvelope;
use App\Repository\DigitalPostEnvelopeRepository;
use ItkDev\Serviceplatformen\Service\SF1601\Serializer;
use ItkDev\Serviceplatformen\Service\SF1601\SF1601;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DigitalPoster
{
    use LoggerAwareTrait;

    private array $options;

    public function __construct(private CertificateLocatorHelper $certificateLocatorHelper, private MeMoHelper $meMoHelper, private DigitalPostEnvelopeRepository $envelopeRepository, LoggerInterface $logger, array $options)
    {
        $this->options = $this->resolveOptions($options);
        $this->setLogger($logger);
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
                $envelope->getMessageUuid(),
                $digitalPost,
                $recipient
            ));
        }

        try {
            $meMoOptions = [
                MeMoHelper::SENDER_IDENTIFIER_TYPE => MeMoHelper::IDENTIFIER_TYPE_CVR,
                MeMoHelper::SENDER_IDENTIFIER => $this->options['sf1601']['authority_cvr'],
            ];

            $meMoMessage = $this->meMoHelper->createMeMoMessage($digitalPost, $recipient, $meMoOptions);
            $messageUuid = $meMoMessage->getMessageHeader()->getMessageUUID();

            $options = $this->options['sf1601']
                + [
                    'certificate_locator' => $this->certificateLocatorHelper->getCertificateLocator(),
                ];
            $service = new SF1601($options);
            $transactionId = Serializer::createUuid();
            $response = $service->kombiPostAfsend($transactionId, SF1601::TYPE_AUTOMATISK_VALG, $meMoMessage);

            $serializer = new Serializer();
            $receipt = $response->getContent();

            // We don't want to store actual document content in the envelope.
            $body = $meMoMessage->getMessageBody();
            $body->getMainDocument()->setFile([]);
            $body->setAdditionalDocument([]);

            $envelope
                ->setStatus(DigitalPostEnvelope::STATUS_SENT)
                ->setMessage($serializer->serialize($meMoMessage))
                ->setMessageUuid($messageUuid)
                ->setReceipt($receipt)
            ;

            $this->envelopeRepository->save($envelope, true);

            $this->logger->info(
                $isNew
                    ? sprintf(
                    'Created envelope %s for digital post %s to %s',
                    $envelope->getMessageUuid(),
                    $digitalPost,
                    $recipient
                )
                    : sprintf(
                    'Reused envelope %s for digital post %s to %s',
                    $envelope->getMessageUuid(),
                    $digitalPost,
                    $recipient
                )
            );
        } catch (\Throwable $throwable) {
            $envelope
                ->setStatus(DigitalPostEnvelope::STATUS_FAILED)
                ->setStatusMessage($throwable->getMessage())
            ;

            $this->envelopeRepository->save($envelope, true);
        }
    }

    private function resolveOptions(array $options): array
    {
        return (new OptionsResolver())
            ->setRequired('sf1601')
            ->setAllowedTypes('sf1601', 'array')
            ->resolve($options)
        ;
    }
}
