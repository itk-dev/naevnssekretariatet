<?php

namespace App\Service;

use App\Entity\BBRData;
use App\Entity\CaseEntity;
use App\Repository\BBRDataRepository;
use Doctrine\ORM\EntityManagerInterface;
use ItkDev\Datafordeler\Client;
use ItkDev\Datafordeler\Service\BBR\BBRPublic\V1 as BBRPublicV1;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class BBRHelper
{
    private BBRDataRepository $bbrDataRepository;
    private EntityManagerInterface $entityManager;
    private HttpClientInterface $httpClient;

    public function __construct(BBRDataRepository $bbrDataRepository, EntityManagerInterface $entityManager, HttpClientInterface $httpClient, array $bbrHelperOptions)
    {
        $this->bbrDataRepository = $bbrDataRepository;
        $this->entityManager = $entityManager;
        $this->httpClient = $httpClient;
        $this->options = $bbrHelperOptions;

        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);

        $this->options = $resolver->resolve($bbrHelperOptions);
    }

    public function updateBBRData(CaseEntity $case, string $addressType, bool $dryRun): ?array
    {
        $address = $case->getFormattedAddress($addressType);
        $bbrData = $this->bbrDataRepository->findOneBy(['address' => $address]);
        if (null === $bbrData) {
            $data = $this->fetchBBRData($address);
            $bbrData = (new BBRData())
                ->setAddress($address)
                ->setData($data)
            ;
        }

        header('content-type: text/plain');
        echo var_export([$address, $data], true);
        exit(__FILE__.':'.__LINE__.':'.__METHOD__);
    }

    public function getBBRData(string $address): ?BBRData
    {
        $bbrData = $this->bbrDataRepository->findOneBy(['address' => $address]);
        if (null === $bbrData) {
            $data = $this->fetchBBRData($address);
            if (null !== $data) {
                $bbrData = (new BBRData())
                    ->setAddress($address)
                    ->setData($data)
                ;
            }
        } else {
            // @todo Update BBR data? When?
        }

        if (null !== $bbrData) {
            $this->entityManager->persist($bbrData);
            $this->entityManager->flush();
        }

        return $bbrData;
    }

    private function fetchBBRData(string $address): ?array
    {
        $bbrData = null;

        // @see https://dawadocs.dataforsyningen.dk/dok/bbr#find-en-enhed-ud-fra-dens-adresse
        $response = $this->httpClient->request('GET', 'https://api.dataforsyningen.dk/adresser', [
            'query' => ['q' => $address],
        ]);
        $data = $response->toArray();
        if (1 !== count($data) || !isset($data[0]['id'])) {
            throw new \InvalidArgumentException(sprintf('Invalid, unknown or ambiguous address: %s', $address));
        }
        $addressId = $data[0]['id'];

        //$addressId = '0a3f509e-6716-32b8-e044-0003ba298018';

        try {
            $client = Client::createFromUsernameAndPassword(
                $this->options['datafordeler_api_username'],
                $this->options['datafordeler_api_password']
            );
            $service = new BBRPublicV1($client);

            $bbrData['enhed'] = $service->enhed(['AdresseIdentificerer' => $addressId]);
        } catch (\Exception $exception) {
        }

        return $bbrData;
    }

    /**
     * Get url to BBR-Meddelelse for an address.
     *
     * @param string $address the address
     * @param string $format  The format. Current only 'pdf' is supported.
     *
     * @return string The url to the BBR-Meddelelse
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     * @throws \InvalidArgumentException
     */
    public function getBBRMeddelelseUrl(string $address, string $format = 'pdf'): string
    {
        if ('pdf' !== $format) {
            throw new \InvalidArgumentException(sprintf('Invalid format: %s', $format));
        }

        // @see https://dawadocs.dataforsyningen.dk/dok/bbr#find-en-bygning-ud-fra-dens-adgangsadresse
        $response = $this->httpClient->request('GET', 'https://api.dataforsyningen.dk/adgangsadresser', [
            'query' => ['q' => $address],
        ]);
        $data = $response->toArray();
        if (1 !== count($data) || !isset($data[0]['id'])) {
            throw new \InvalidArgumentException(sprintf('Invalid, unknown or ambiguous address: %s', $address));
        }
        $accessAddressId = $data[0]['id'];

        $response = $this->httpClient->request('GET', 'https://api.dataforsyningen.dk/bbrlight/opgange', [
            'query' => ['adgangsadresseid' => $accessAddressId],
        ]);
        $data = $response->toArray();
        if (1 !== count($data) || !isset($data[0]['Bygning_id'])) {
            throw new \InvalidArgumentException(sprintf('Cannot get building id for address %s', $address));
        }
        $buildingId = $data[0]['Bygning_id'];

        $response = $this->httpClient->request('GET', 'https://api.dataforsyningen.dk/bbrlight/bygninger', [
            'query' => ['id' => $buildingId],
        ]);
        $data = $response->toArray();
        if (1 !== count($data) || !isset($data[0]['KomKode'], $data[0]['ESREjdNr'])) {
            throw new \InvalidArgumentException(sprintf('Cannot get building id for address %s', $address));
        }
        ['KomKode' => $municipalityCode, 'ESREjdNr' => $propertyIdentifier] = $data[0];

        return 'https://bbr.dk/pls/wwwdata/get_ois_pck.show_bbr_meddelelse_pdf?'.http_build_query([
                'i_municipalitycode' => $municipalityCode,
                'i_realpropertyidentifier' => $propertyIdentifier,
            ]);
    }

    private function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['datafordeler_api_username', 'datafordeler_api_password']);
    }
}
