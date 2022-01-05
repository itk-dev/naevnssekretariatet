<?php

namespace App\Service;

use App\Entity\BBRData;
use App\Entity\CaseEntity;
use App\Entity\Embeddable\Address;
use App\Exception\BBRException;
use App\Repository\BBRDataRepository;
use Doctrine\ORM\EntityManagerInterface;
use ItkDev\Datafordeler\Client;
use ItkDev\Datafordeler\Service\BBR\V1\BBRPublic;
use ItkDev\Datafordeler\Service\DAR\V1\DAR;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;

class BBRHelper implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    private PropertyAccessorInterface $propertyAccessor;
    private BBRDataRepository $bbrDataRepository;
    private EntityManagerInterface $entityManager;
    private HttpClientInterface $httpClient;
    private array $options;

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

        if (null === $bbrData->getData()
            || $bbrData->getUpdatedAt() < new \DateTimeImmutable(sprintf('-%dseconds', (int) $this->options['bbr_data_ttl']))) {
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

            $darService = new DAR($client);
            $husnummer = $darService->adresseTilHusnummer($addressId);
            $bbrService = new BBRPublic($client);

            $bbrData['bygning'] = $bbrService->bygning(['Husnummer' => $husnummer]);

            $bbrData['enhed'] = $bbrService->enhed(['AdresseIdentificerer' => $addressId]);
        } catch (\Exception $exception) {
            throw $this->createException($exception->getMessage(), $exception->getCode(), $exception);
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
            throw $this->createException(sprintf('Invalid format: %s', $format));
        }

        $addressData = $this->getAddressData($address);
        if (!isset($addressData['adgangsadresse']['id'])) {
            throw $this->createException(sprintf('Cannot get adgangsadresse for address %s', $address));
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
            throw $this->createException(sprintf('Invalid or unknown address: %s', $address));
        }

        return 'https://bbr.dk/pls/wwwdata/get_ois_pck.show_bbr_meddelelse_pdf?'.http_build_query([
                'i_municipalitycode' => $item['MunicipalityCode'],
                'i_realpropertyidentifier' => $item['MunicipalRealPropertyIdentifier'],
            ]);
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
            throw $this->createException(sprintf('Property %s.%s must be an instance of %s; is %s', get_class($entity), $property, Address::class, get_class($address)));
        }

        return $address;
    }

    private function createException(string $message, $code = 0, Throwable $previous = null)
    {
        $this->logger->error($message, ['previous' => $previous]);

        return new BBRException($message, $code, $previous);
    }

    /**
     * Configure options.
     */
    private function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['datafordeler_api_username', 'datafordeler_api_password', 'bbr_data_ttl']);
    }

    /**
     * Get an address object from a stringified address using Adgangsadresse datavask.
     *
     * @see https://dawadocs.dataforsyningen.dk/dok/api/adgangsadresse#datavask
     */
    public function getAccessAddressData(string $address): array
    {
        return $this->fetchAddressData($address, 'adgangsadresser');
    }

    /**
     * Get an address object from a stringified address using Adresse datavask.
     *
     * @see https://dawadocs.dataforsyningen.dk/dok/api/adresse#datavask
     */
    public function getAddressData(string $address): array
    {
        return $this->fetchAddressData($address, 'adresser');
    }

    private function fetchAddressData(string $address, string $path): array
    {
        try {
            $client = HttpClient::create([
                'base_uri' => 'https://api.dataforsyningen.dk/datavask/',
            ]);

            $response = $client->request('GET', $path, [
                'query' => [
                    'betegnelse' => $address,
                ],
            ]);

            $data = $response->toArray();
            if (in_array($data['kategori'] ?? null, ['A', 'B'])
                && isset($data['resultater'][0]['adresse'])) {
                return $data['resultater'][0]['adresse'];
            }
        } catch (\Throwable $throwable) {
            throw $this->createException(sprintf('Invalid address: %s', $address), $throwable->getCode(), $throwable);
        }

        throw $this->createException(sprintf('Invalid address: %s', $address));
    }
}
