<?php

namespace App\Service;

use App\Entity\Embeddable\Address;
use ItkDev\Serviceplatformen\DigitalPost\DigitalPost;
use ItkDev\Serviceplatformen\SF1600\EnumType\KanalvalgType;
use ItkDev\Serviceplatformen\SF1600\EnumType\PrioritetType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\String\ByteString;
use Symfony\Component\Uid\Uuid;

class DigitalPostHelper extends DigitalPost
{
    private array $serviceOptions;

    public function __construct(array $options)
    {
        parent::__construct();
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);

        $this->serviceOptions = $resolver->resolve($options);
    }

    public function sendDigitalPost(string $cpr, string $name, Address $address, string $title, string $content): bool
    {
        $result = $this->setServiceOptions($this->serviceOptions['digital_post_options'])
            ->afsendBrevPerson(
                $channel = KanalvalgType::VALUE_A,
                $priority = PrioritetType::VALUE_D,
                $cprNummerIdentifikator = $cpr,
                $personName = $name,
                $coNavn = null,
                $streetName = $address->getStreet(),
                $streetBuildingIdentifier = $address->getNumber(),
                $floorIdentifier = $address->getFloor().($address->getSide() ? ' '.$address->getSide() : ''),
                $suiteIdentifier = null,
                $mailDeliverySublocationIdentifier = null,
                $postCodeIdentifier = $address->getPostalCode(),
                $districtSubdivisionIdentifier = null,
                $postOfficeBoxIdentifier = null,
                $countryIdentificationCode = null,
                $filFormatNavn = 'PDF',
                $meddelelseIndholdData = $content,
                $titelTekst = $title,
                $brevDato = null
            )
        ;

        header('content-type: text/plain');
        echo var_export($result, true);
        exit(__FILE__.':'.__LINE__.':'.__METHOD__);
    }

    protected function generateNextSerialNumber(): string
    {
        ByteString::fromRandom(21)->toString();
    }

    protected function generateUUID(): string
    {
        return Uuid::v4()->toRfc4122();
    }

    protected function acquireLock(): bool
    {
        return true;
    }

    protected function releaseLock()
    {
        return;
    }

    protected function waitLock(): bool
    {
        return true;
    }

    private function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired([
            'digital_post_options',
        ]);
    }
}
