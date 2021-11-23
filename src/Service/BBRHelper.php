<?php

namespace App\Service;

use App\Entity\BBRData;
use App\Entity\CaseEntity;
use App\Entity\Embeddable\Address;
use App\Exception\BBRException;
use App\Repository\BBRDataRepository;
use Doctrine\ORM\EntityManagerInterface;
use ItkDev\Datafordeler\Client;
use ItkDev\Datafordeler\Service\BBR\BBRPublic\V1 as BBRPublicV1;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class BBRHelper
{
    private PropertyAccessorInterface $propertyAccessor;
    private BBRDataRepository $bbrDataRepository;
    private EntityManagerInterface $entityManager;
    private HttpClientInterface $httpClient;

    public function __construct(PropertyAccessorInterface $propertyAccessor, BBRDataRepository $bbrDataRepository, EntityManagerInterface $entityManager, HttpClientInterface $httpClient, array $bbrHelperOptions)
    {
        $this->propertyAccessor = $propertyAccessor;
        $this->bbrDataRepository = $bbrDataRepository;
        $this->entityManager = $entityManager;
        $this->httpClient = $httpClient;
        $this->options = $bbrHelperOptions;

        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);

        $this->options = $resolver->resolve($bbrHelperOptions);
    }

    public function updateCaseBBRData(CaseEntity $case, string $property): ?array
    {
        $address = $this->getAddress($case, $property);
        $bbrData = $this->getBBRData((string) $address);
        if (null !== $bbrData) {
            $address->setBBRData($bbrData->getData());
        }

        return $address->getBBRData();
    }

    public function getBBRData(string $address): ?BBRData
    {
        $address = $this->normalizeAddress($address);
        $bbrData = $this->bbrDataRepository->findOneBy(['address' => $address]);
        if (null === $bbrData) {
            $bbrData = (new BBRData())
                ->setAddress($address)
            ;
        }

        if (null === $bbrData->getData() || $bbrData->getUpdatedAt() < new \DateTimeImmutable($this->options['ttl'])) {
            $data = $this->fetchBBRData($address);
            $bbrData->setData($data);
        }

        $this->entityManager->persist($bbrData);
        $this->entityManager->flush();

        return $bbrData;
    }

    private function fetchBBRData(string $address): ?array
    {
        $bbrData = null;

        $addressData = $this->getAddressData($address);

        $bbrData['address'] = $addressData;

        $addressId = $addressData['id'];

        try {
            $client = Client::createFromUsernameAndPassword(
                $this->options['datafordeler_api_username'],
                $this->options['datafordeler_api_password']
            );
            $service = new BBRPublicV1($client);

            $bbrData['enhed'] = $service->enhed(['AdresseIdentificerer' => $addressId]);
            $bbrData['bygning'] = $service->enhed(['AdresseIdentificerer' => $addressId]);
        } catch (\Exception $exception) {
        }

        return $bbrData;
    }

    /**
     * Normalize address.
     *
     * @return array|string|string[]|null
     */
    private function normalizeAddress(string $address)
    {
        // Trim and convert newlines to a single space.
        $address = preg_replace('/[\r\n]+/', ' ', trim($address));
        // Collapse multiple spaces to a single space.
        // @see https://stackoverflow.com/a/42576699
        return preg_replace('/[^\S\r\n]+/', ' ', $address);
    }

    public function getBBRMeddelelseUrlForCase(CaseEntity $case, string $addressProperty, string $format = 'pdf'): string
    {
        $address = $this->getAddress($case, $addressProperty);

        return $this->getBBRMeddelelseUrl((string) $address, $format);
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

        $addressData = $this->getAddressData($address);
        if (!isset($addressData['adgangsadresse']['id'])) {
            throw new BBRException(sprintf('Cannot get adgangsadresse for address %s', $address));
        }
        $accessAddressId = $addressData['adgangsadresse']['id'];

        $response = $this->httpClient->request('GET', 'https://bbr.dk/pls/wwwdata/get_ois_pck.nyuuid2ejd', [
            'query' => ['i_addressid' => $accessAddressId],
        ]);

        $xml = new \SimpleXMLElement($response->getContent());
        // Convert to list of associative arrays.
        $items = [];
        foreach ($xml->RealProperty as $el) {
            $items[] = json_decode(json_encode($el), true);
        }

        // Dig for best match
        $item = $this->findBestAddressMatch($this->normalizeAddress($address), $items, 'Adresse');
        if (null === $item) {
            throw new BBRException(sprintf('Invalid or unknown address: %s', $address));
        }

        return 'https://bbr.dk/pls/wwwdata/get_ois_pck.show_bbr_meddelelse_pdf?'.http_build_query([
                'i_municipalitycode' => $item['MunicipalityCode'],
                'i_realpropertyidentifier' => $item['MunicipalRealPropertyIdentifier'],
            ]);
    }

    /**
     * Find address data by (stringified) address.
     *
     * @throws \InvalidArgumentException
     */
    private function getAddressData(string $address): array
    {
        try {
            // @see https://dawadocs.dataforsyningen.dk/dok/bbr#find-en-enhed-ud-fra-dens-adresse
            $response = $this->httpClient->request('GET', 'https://api.dataforsyningen.dk/adresser', [
                'query' => ['q' => $this->normalizeAddress($address)],
            ]);
            $data = $response->toArray();
        } catch (\Exception $exception) {
            throw new BBRException(sprintf('Invalid or unknown address: %s', $address), 0, $exception);
        }
        if (0 === count($data) || !isset($data[0]['id'])) {
            throw new BBRException(sprintf('Invalid or unknown address: %s', $address));
        }

        $item = $this->findBestAddressMatch($address, $data, 'adressebetegnelse');
        if (null === $item) {
            throw new BBRException(sprintf('Invalid or unknown address: %s', $address));
        }

        return $item;
    }

    /**
     * Find best address match in a list of address objects.
     *
     * @return float|mixed|null
     */
    private function findBestAddressMatch(string $value, array $items, string $key)
    {
        $bestMatch = [
            'percentage' => 0.0,
        ];
        foreach ($items as $item) {
            if (isset($item[$key])) {
                similar_text($value, $this->normalizeAddress($item[$key]), $percentage);
                if ($percentage > $bestMatch['percentage']) {
                    $bestMatch['percentage'] = $percentage;
                    $bestMatch['item'] = $item;
                }
            }
        }

        return $bestMatch['item'] ?? null;
    }

    private function getAddress($entity, string $property): Address
    {
        $address = $this->propertyAccessor->getValue($entity, $property);
        if (!($address instanceof Address)) {
            throw new BBRException(sprintf('Property %s.%s must be an instance of %s; is %s', get_class($entity), $property, Address::class, get_class($address)));
        }

        return $address;
    }

    /**
     * Configure options.
     */
    private function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['datafordeler_api_username', 'datafordeler_api_password', 'ttl']);
    }
}
