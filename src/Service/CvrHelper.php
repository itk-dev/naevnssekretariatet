<?php

namespace App\Service;

use App\Entity\CaseEntity;
use App\Entity\Embeddable\Identification;
use App\Exception\CvrException;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Http\Adapter\Guzzle7\Client as GuzzleAdapter;
use Http\Factory\Guzzle\RequestFactory;
use ItkDev\AzureKeyVault\Authorisation\VaultToken;
use ItkDev\AzureKeyVault\Exception\SecretException;
use ItkDev\AzureKeyVault\Exception\TokenException;
use ItkDev\AzureKeyVault\KeyVault\VaultSecret;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class CvrHelper
{
    /**
     * @var array {
     *   lookup_service_url: string,
     *   lookup_api_key: string,
     * }
     */
    private array $serviceOptions;

    public function __construct(private CaseManager $caseManager, private EntityManagerInterface $entityManager, private PropertyAccessorInterface $propertyAccessor, private TranslatorInterface $translator, private HttpClientInterface $httpClient, array $options)
    {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);

        $this->serviceOptions = $resolver->resolve($options);
    }

    private function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired([
                'lookup_service_url',
                'lookup_api_key',
            ],
            )
        ;
    }

    /**
     * @throws CvrException
     */
    public function lookupCvr(string $cvr): array
    {
        try {
            return $this->executeQuery($cvr)->toArray();
        } catch (ExceptionInterface $e) {
            throw new CvrException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Lifted from https://github.com/OS2web/os2web_datalookup/blob/main/src/Plugin/os2web/DataLookup/DatafordelerCVR.php.
     *
     * Executes the GraphQL lookup request for a specific CVR number.
     *
     * Builds the GraphQL payload and sends it to the configured Datafordeler
     * endpoint.
     *
     * @see https://datafordeler.dk/dataoversigt/det-centrale-virksomhedsregister-cvr/cvr-graphql/
     *
     * @throws TransportExceptionInterface
     */
    private function executeQuery(string $cvr): ResponseInterface {
        // Setting date to TODAY 00:00:00, so that we are always getting up-to-date
        // information.
        $virkningstid = (new \DateTimeImmutable('today', new \DateTimeZone('UTC')))
            ->format('Y-m-d\T00:00:00\Z');

        $query = <<<GRAPHQL
{ CVR_Virksomhed(first: 1, virkningstid: "{$virkningstid}", where: { CVRNummer: { eq: {$cvr} } }) {
    nodes {
      CVRNummer
      id_CVR_CVREnhed_id_ref(first: 1) {
        nodes {
          id_CVR_Navn_CVREnhedsId_ref { vaerdi }
          id_CVR_Adressering_CVREnhedsId_ref(first: 1, where: { AdresseringAnvendelse: { in: ["beliggenhedsadresse", "postadresse"] } }) {
            nodes {
              AdresseringAnvendelse
              CVRAdresse_vejnavn
              CVRAdresse_husnummerFra
              CVRAdresse_etagebetegnelse
              CVRAdresse_doerbetegnelse
              CVRAdresse_postnummer
              CVRAdresse_postdistrikt
              CVRAdresse_kommunekode
            }
          }
        }
      }
    }
  }
}
GRAPHQL;

        $webserviceUrl = $this->serviceOptions['lookup_service_url'];

        return $this->httpClient->request(Request::METHOD_POST, $webserviceUrl, [
            'query' => [
                'apiKey' => $this->serviceOptions['lookup_api_key'],
            ],
            'json' => [
                'query' => $query,
            ],
        ]);
    }

    /**
     * Validates that case data agree with CVR lookup data.
     *
     * @throws CvrException
     */
    public function validateCvr(CaseEntity $case, string $idProperty, string $addressProperty, string $nameProperty): bool
    {
        $caseIdentificationRelevantData = $this->caseManager->getCaseIdentificationValues($case, $addressProperty, $nameProperty);

        /** @var Identification $id */
        $id = $this->propertyAccessor->getValue($case, $idProperty);

        $cvrData = $this->lookupCvr($id->getIdentifier());
        $cvrDataArray = json_decode(json_encode($cvrData), true);

        $cvrIdentificationRelevantData = $this->collectRelevantData($cvrDataArray);

        if ($caseIdentificationRelevantData != $cvrIdentificationRelevantData) {
            throw new CvrException($this->translator->trans('Case data not match CVR data', [], 'case'));
        }

        $id->setValidatedAt(new \DateTime('now'));
        $this->entityManager->flush();

        return true;
    }

    public function collectRelevantData(array $data): array
    {
        $relevantData = [];

        $name = $data['data']['CVR_Virksomhed']['nodes']['0']['id_CVR_CVREnhed_id_ref']['nodes']['0']['id_CVR_Navn_CVREnhedsId_ref']['vaerdi'] ?? '';
        $relevantData['name'] = $name;

        $addresses = (array)($data['data']['CVR_Virksomhed']['nodes']['0']['id_CVR_CVREnhed_id_ref']['nodes']['0']['id_CVR_Adressering_CVREnhedsId_ref']['nodes'] ?? []);
        foreach ($addresses as $address) {
            if ('beliggenhedsadresse' === ($address['AdresseringAnvendelse'] ?? null)) {
                $relevantData['street'] = $address['CVRAdresse_vejnavn'] ?? '';
                $relevantData['number'] = $address['CVRAdresse_husnummerFra'] ?? '';
                $relevantData['floor'] = $address['CVRAdresse_etagebetegnelse'] ?? '';
                $relevantData['side'] = $address['CVRAdresse_doerbetegnelse'] ?? '';
                $relevantData['postalCode'] = $address['CVRAdresse_postnummer'] ?? '';
                $relevantData['city'] = $address['CVRAdresse_postdistrikt'] ?? '';
            }
        }

        return $relevantData;
    }

}
