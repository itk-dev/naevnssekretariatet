<?php

namespace App\Service;

use App\Entity\BBRData;
use App\Entity\CaseEntity;
use App\Entity\Embeddable\Address;
use App\Exception\AddressException;
use App\Exception\BBRException;
use App\Repository\BBRDataRepository;
use Doctrine\ORM\EntityManagerInterface;
use ItkDev\Datafordeler\Client;
use ItkDev\Datafordeler\Service\BBR\V1\BBRPublic;
use ItkDev\Datafordeler\Service\DAR\V1\DAR;
use ItkDev\Datafordeler\Service\DAR\V1\DAR_BFE_Public;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class BBRHelper implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    private array $options;

    public function __construct(private AddressHelper $addressHelper, private PropertyAccessorInterface $propertyAccessor, private BBRDataRepository $bbrDataRepository, private EntityManagerInterface $entityManager, private HttpClientInterface $httpClient, private TranslatorInterface $translator, array $bbrHelperOptions)
    {
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

            $bygning = $bbrService->bygning(['Husnummer' => $husnummer]);
            // Sort by building number.
            usort($bygning, static fn ($a, $b) => (($a['byg007Bygningsnummer'] ?? null) <=> ($b['byg007Bygningsnummer'] ?? null)));

            // Try to find a BFE number from husnummer using the DAR BFE service.
            $bfeNummer = (function () use ($husnummer, $client) {
                $darBfeService = new DAR_BFE_Public($client);

                if ($bfe = $darBfeService->husnummerTilBygningBfe($husnummer)) {
                    return $bfe['jordstykkeList'][0]['samletFastEjendom'] ?? null;
                }

                // @todo will we ever end up here?
                if ($bfe = $darBfeService->husnummerTilBygningBfe($husnummer)) {
                    // @todo return ?
                }

                return null;
            })();

            if (null !== $bfeNummer) {
                $bbrData['ejendomsrelation'] = $bbrService->ejendomsrelation(['BFENummer' => $bfeNummer]);
            }

            $bbrData['bygning'] = $bygning;

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
     * @return string the url to the BBR-Meddelelse if any
     */
    public function getBBRMeddelelseUrl(string $address, string $format = 'pdf'): ?string
    {
        if ('pdf' !== $format) {
            throw $this->createException(sprintf('Invalid format: %s', $format));
        }

        $bbrData = $this->getBBRData($address);
        $ejendomsrelation = $bbrData->getData()['ejendomsrelation'] ?? null;
        if (isset($ejendomsrelation[0]['bfeNummer'])) {
            return 'https://bbr.dk/pls/wwwdata/get_newois_pck.show_bbr_meddelelse_pdf?'.http_build_query([
                'i_bfe' => $ejendomsrelation[0]['bfeNummer'],
            ]);
        }

        throw $this->createException($this->translator->trans('Cannot get url for BBR-meddelelse for {address}', ['address' => $address], 'case'));
    }

    private function getAddress($entity, string $property): Address
    {
        $address = $this->propertyAccessor->getValue($entity, $property);
        if (!($address instanceof Address)) {
            throw $this->createException(sprintf('Property %s.%s must be an instance of %s; is %s', get_class($entity), $property, Address::class, get_class($address)));
        }

        return $address;
    }

    private function createException(string $message, $code = 0, ?\Throwable $previous = null)
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
     * @throws BBRException
     */
    public function getAddressData(string $address): array
    {
        try {
            $addressData = $this->addressHelper->fetchAddressData($address);
        } catch (AddressException $e) {
            throw $this->createException($e->getMessage(), $e->getCode(), $e);
        }

        return $addressData;
    }
}
