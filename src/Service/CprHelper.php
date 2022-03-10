<?php

namespace App\Service;

use App\Entity\CaseEntity;
use App\Entity\Embeddable\Address;
use App\Entity\Embeddable\Identification;
use App\Entity\FenceReviewCase;
use App\Exception\CprException;
use Doctrine\Bundle\DoctrineBundle\EventSubscriber\EventSubscriberInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use GuzzleHttp\Client;
use Http\Adapter\Guzzle7\Client as GuzzleAdapter;
use Http\Factory\Guzzle\RequestFactory;
use ItkDev\AzureKeyVault\Authorisation\VaultToken;
use ItkDev\AzureKeyVault\KeyVault\VaultSecret;
use ItkDev\Serviceplatformen\Certificate\AzureKeyVaultCertificateLocator;
use ItkDev\Serviceplatformen\Certificate\CertificateLocatorInterface;
use ItkDev\Serviceplatformen\Certificate\Exception\CertificateLocatorException;
use ItkDev\Serviceplatformen\Request\InvocationContextRequestGenerator;
use ItkDev\Serviceplatformen\Service\Exception\NoPnrFoundException;
use ItkDev\Serviceplatformen\Service\Exception\ServiceException;
use ItkDev\Serviceplatformen\Service\PersonBaseDataExtendedService;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class CprHelper implements EventSubscriberInterface
{
    /**
     * The client.
     */
    private Client $guzzleClient;
    private array $serviceOptions;
    private PersonBaseDataExtendedService $service;

    public function __construct(private PropertyAccessorInterface $propertyAccessor, private EntityManagerInterface $entityManager, private TranslatorInterface $translator, array $options)
    {
        $this->guzzleClient = new Client();
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);

        $this->serviceOptions = $resolver->resolve($options);
    }

    /**
     * @throws CprException
     */
    public function lookupCPR(string $cpr)
    {
        if (!isset($this->service)) {
            $this->setupService();
        }

        try {
            $response = $this->service->personLookup($cpr);
        } catch (NoPnrFoundException $e) {
            throw new CprException($this->translator->trans('PNR not found', [], 'case'), $e->getCode(), $e);
        } catch (ServiceException $e) {
            throw new CprException($e->getMessage(), $e->getCode(), $e);
        }

        return $response;
    }

    /**
     * @throws CprException
     */
    private function setupService()
    {
        $certificateLocator = $this->getAzureKeyVaultCertificateLocator(
            $this->serviceOptions['azure_tenant_id'],
            $this->serviceOptions['azure_application_id'],
            $this->serviceOptions['azure_client_secret'],
            $this->serviceOptions['azure_key_vault_name'],
            $this->serviceOptions['azure_key_vault_secret'],
            $this->serviceOptions['azure_key_vault_secret_version']
        );

        try {
            $pathToCertificate = $certificateLocator->getAbsolutePathToCertificate();
        } catch (CertificateLocatorException $e) {
            throw new CprException($e->getMessage(), $e->getCode());
        }

        $options = [
            'local_cert' => $pathToCertificate,
            'passphrase' => $certificateLocator->getPassphrase(),
            'location' => $this->serviceOptions['serviceplatformen_cpr_service_endpoint'],
        ];

        if (!realpath($this->serviceOptions['serviceplatformen_cpr_service_contract'])) {
            throw new CprException(sprintf('The path (%s) to the service contract is invalid.', $this->serviceOptions['serviceplatformen_cpr_service_contract']));
        }

        try {
            $soapClient = new \SoapClient($this->serviceOptions['serviceplatformen_cpr_service_contract'], $options);
        } catch (\SoapFault $e) {
            throw new CprException($e->getMessage(), $e->getCode());
        }

        $requestGenerator = new InvocationContextRequestGenerator(
            $this->serviceOptions['serviceplatformen_cpr_service_agreement_uuid'],
            $this->serviceOptions['serviceplatformen_cpr_user_system_uuid'],
            $this->serviceOptions['serviceplatformen_cpr_service_uuid'],
            $this->serviceOptions['serviceplatformen_cpr_user_uuid']
        );

        $this->service = new PersonBaseDataExtendedService($soapClient, $requestGenerator);
    }

    private function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired([
                    'azure_tenant_id',
                    'azure_application_id',
                    'azure_client_secret',
                    'azure_key_vault_name',
                    'azure_key_vault_secret',
                    'azure_key_vault_secret_version',
                    'serviceplatformen_cpr_service_agreement_uuid',
                    'serviceplatformen_cpr_user_system_uuid',
                    'serviceplatformen_cpr_user_uuid',
                    'serviceplatformen_cpr_service_uuid',
                    'serviceplatformen_cpr_service_endpoint',
                    'serviceplatformen_cpr_service_contract',
                ],
            )
        ;
    }

    /**
     * Get absolute path to certificate.
     */
    private function getAzureKeyVaultCertificateLocator(
        string $tenantId,
        string $applicationId,
        string $clientSecret,
        string $keyVaultName,
        string $keyVaultSecret,
        string $keyVaultSecretVersion
    ): CertificateLocatorInterface {
        $httpClient = new GuzzleAdapter($this->guzzleClient);
        $requestFactory = new RequestFactory();

        $vaultToken = new VaultToken($httpClient, $requestFactory);

        $token = $vaultToken->getToken(
            $tenantId,
            $applicationId,
            $clientSecret
        );

        $vault = new VaultSecret(
            $httpClient,
            $requestFactory,
            $keyVaultName,
            $token->getAccessToken()
        );

        return new AzureKeyVaultCertificateLocator(
            $vault,
            $keyVaultSecret,
            $keyVaultSecretVersion
        );
    }

    /**
     * Validates that case data agree with CPR lookup data.
     *
     * @throws CprException
     */
    public function validateCpr(CaseEntity $case, string $idProperty, string $addressProperty, string $nameProperty): bool
    {
        /** @var Identification $id */
        $id = $this->propertyAccessor->getValue($case, $idProperty);
        /** @var Address $address */
        $address = $this->propertyAccessor->getValue($case, $addressProperty);
        $name = $this->propertyAccessor->getValue($case, $nameProperty);

        $data = [];
        $data['name']['caseData'] = $name;
        $data['street']['caseData'] = $address->getStreet();
        $data['number']['caseData'] = $address->getNumber();
        $data['floor']['caseData'] = $address->getFloor() ?? [];
        $data['side']['caseData'] = $address->getSide() ?? '';
        $data['postalCode']['caseData'] = $address->getPostalCode();
        $data['city']['caseData'] = $address->getCity();

        $cprData = $this->lookupCPR($id->getIdentifier());
        $cprDataArray = json_decode(json_encode($cprData), true);

        $data['name']['cprData'] = $cprDataArray['persondata']['navn']['personadresseringsnavn'];
        $data['street']['cprData'] = $cprDataArray['adresse']['aktuelAdresse']['vejadresseringsnavn'];
        $data['number']['cprData'] = ltrim($cprDataArray['adresse']['aktuelAdresse']['husnummer'], '0');
        $data['floor']['cprData'] = array_key_exists('etage', $cprDataArray['adresse']['aktuelAdresse']) ? $cprDataArray['adresse']['aktuelAdresse']['etage'] : '';
        $data['side']['cprData'] = array_key_exists('sidedoer', $cprDataArray['adresse']['aktuelAdresse']) ? ltrim($cprDataArray['adresse']['aktuelAdresse']['sidedoer'], '0') : '';
        $data['postalCode']['cprData'] = $cprDataArray['adresse']['aktuelAdresse']['postnummer'];
        $data['city']['cprData'] = $cprDataArray['adresse']['aktuelAdresse']['postdistrikt'];

        foreach ($data as $comparisonValues) {
            if ($comparisonValues['caseData'] != $comparisonValues['cprData']) {
                throw new CprException($this->translator->trans('Case data not match CPR register data', [], 'case'));
            }
        }

        $id->setValidatedAt(new \DateTime('now'));
        $this->entityManager->flush();

        return true;
    }

    /**
     * Set Identification.validatedAt to null when changing relevant properties.
     */
    public function preUpdate(PreUpdateEventArgs $args)
    {
        $object = $args->getObject();
        $changeSet = $args->getEntityChangeSet();
        foreach ($changeSet as $propertyPath => $value) {
            if (!$object instanceof CaseEntity) {
                continue;
            }

            // Check that changed property warrants a change of complainant validatedAt
            if (str_contains($propertyPath, 'complainant') && !in_array($propertyPath, $object->getNonRelevantComplainantPropertiesWithRespectToValidation()) && !str_contains($propertyPath, 'validatedAt')) {
                $object->getComplainantIdentification()->setValidatedAt(null);
            }

            // If FenceReviewCase we should also check accused properties
            if ($object instanceof FenceReviewCase) {
                if (str_contains($propertyPath, 'accused') && !in_array($propertyPath, $object->getNonRelevantAccusedPropertiesWithRespectToValidation()) && !str_contains($propertyPath, 'validatedAt')) {
                    $object->getAccusedIdentification()->setValidatedAt(null);
                }
            }
        }
    }

    public function getSubscribedEvents()
    {
        return [
            Events::preUpdate,
        ];
    }
}
