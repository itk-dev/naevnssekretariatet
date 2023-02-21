<?php

namespace App\Service\SF1601;

use App\Entity\DigitalPost;
use App\Entity\DigitalPostEnvelope;
use App\Repository\DigitalPostEnvelopeRepository;
use GuzzleHttp\ClientInterface;
use ItkDev\Serviceplatformen\Service\SF1601\Serializer;
use ItkDev\Serviceplatformen\Service\SF1601\SF1601;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DigitalPoster
{
    private ClientInterface $guzzleClient;
    private array $options;

    public function __construct(private CertificateLocatorHelper $certificateLocatorHelper, private MeMoHelper $meMoHelper, private DigitalPostEnvelopeRepository $envelopeRepository, array $options)
    {
        $this->options = $this->resolveOptions($options);
    }

    public function sendDigitalPost(DigitalPost $digitalPost)
    {
        $meMoOptions = [
            MeMoHelper::SENDER_IDENTIFIER_TYPE => MeMoHelper::IDENTIFIER_TYPE_CVR,
            MeMoHelper::SENDER_IDENTIFIER => $this->options['sf1601']['authority_cvr'],
        ];

        foreach ($digitalPost->getRecipients() as $recipient) {
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

            $envelope = $this->envelopeRepository->findOneBy([
                'digitalPost' => $digitalPost,
                'recipient' => $recipient,
            ]);
            if (null === $envelope) {
                $envelope = (new DigitalPostEnvelope())
                    ->setDigitalPost($digitalPost)
                    ->setRecipient($recipient)
                ;
            }

            $envelope
                ->setMessage($serializer->serialize($meMoMessage))
                ->setMessageUuid($messageUuid)
                ->setReceipt($receipt)
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
