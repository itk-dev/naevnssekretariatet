<?php

namespace App\Service;

use ItkDev\Serviceplatformen\SF1600\ServiceType\Afsend;
use ItkDev\Serviceplatformen\SF1600\StructType\BrevSPBodyType;
use ItkDev\Serviceplatformen\SF1600\StructType\CountryIdentificationCodeType;
use ItkDev\Serviceplatformen\SF1600\StructType\DigitalPostParametreType;
use ItkDev\Serviceplatformen\SF1600\StructType\DokumentParametreType;
use ItkDev\Serviceplatformen\SF1600\StructType\ForsendelseIType;
use ItkDev\Serviceplatformen\SF1600\StructType\ForsendelseModtagerType;
use ItkDev\Serviceplatformen\SF1600\StructType\InvocationContextType;
use ItkDev\Serviceplatformen\SF1600\StructType\KontaktOplysningType;
use ItkDev\Serviceplatformen\SF1600\StructType\PrintAfsendBrevRequestType;
use ItkDev\Serviceplatformen\SF1600\StructType\SlutbrugerIdentitetType;

class DigitalPostHelper
{
    /**
     * The config.
     *
     * @var \Drupal\Core\Config\ImmutableConfig
     */
    private ImmutableConfig $config;

    /**
     * The client.
     *
     * @var \GuzzleHttp\Client
     */
    private Client $guzzleClient;

    /**
     * The lock.
     *
     * @var \Drupal\Core\Lock\LockBackendInterface
     */
    private LockBackendInterface $lock;

    /**
     * The state.
     *
     * @var \Drupal\Core\State\State
     */
    private State $state;

    /**
     * The UUID generator.
     *
     * @var UuidInterface
     */
    private $uuid;

    /**
     * The lock name.
     *
     * @var string
     */
    private $lockName = 'os2forms_digital_post_print_service';

    /**
     * The lock name.
     *
     * @var string
     */
    private $lockName = 'os2forms_digital_post_print_service';

    /**
     * Constructor.
     */
    public function __construct(ConfigFactoryInterface $configFactory, Client $guzzleClient, LockBackendInterface $lock, State $state) {

        $this->config = $configFactory->get('os2forms_digital_post');
        $this->guzzleClient = $guzzleClient;
        $this->lock = $lock;
        $this->state = $state;
        $this->uuid = \Drupal::service('uuid');
    }

    /**
     * Afsend brev person.
     */
    public function afsendBrevPerson(
        string $kanalValg = NULL,
        string $prioritet = NULL,
        string $cprNummerIdentifikator = NULL,
        string $personName = NULL,
        string $coNavn = NULL,
        string $streetName = NULL,
        string $streetBuildingIdentifier = NULL,
        string $floorIdentifier = NULL,
        string $suiteIdentifier = NULL,
        string $mailDeliverySublocationIdentifier = NULL,
        string $postCodeIdentifier = NULL,
        string $districtSubdivisionIdentifier = NULL,
        string $postOfficeBoxIdentifier = NULL,
        string $countryIdentificationCode = NULL,
        string $filFormatNavn = NULL,
        string $meddelelseIndholdData = NULL,
        string $titelTekst = NULL,
        string $brevDato = NULL
    ):  bool {
        if (!$this->acquireLock()) {
            $this->waitLock();
        }

        $invocationContext = new InvocationContextType(
            $this->config->get('service_agreement_uuid'),
            $this->config->get('user_system_uuid'),
            $this->config->get('user_uuid'),
            $this->config->get('service_uuid')
        );

        $slutBrugerIdentitetType = new SlutbrugerIdentitetType($cprNummerIdentifikator);

        $countryIdentificationCodeType = new CountryIdentificationCodeType($countryIdentificationCode);

        $kontaktOplysninger = new KontaktOplysningType(
            $personName,
            $coNavn,
            $streetName,
            $streetBuildingIdentifier,
            $floorIdentifier,
            $suiteIdentifier,
            $mailDeliverySublocationIdentifier,
            $postCodeIdentifier,
            $districtSubdivisionIdentifier,
            $postOfficeBoxIdentifier,
            NULL,
            NULL,
            NULL,
            NULL,
            NULL,
            NULL,
            $countryIdentificationCodeType
        );

        $forsendelsesModtager = new ForsendelseModtagerType($slutBrugerIdentitetType, $kontaktOplysninger);

        $dokumentParametre = new DokumentParametreType($titelTekst, $this->uuid->generate(), $brevDato);
        $digitalPostParametre = new DigitalPostParametreType($brevDato, $this->config->get('digital_post_materiale_id'));

        $forsendelse = new ForsendelseIType(
            $this->generateAfsendelseIdentifikator(),
            $this->config->get('digital_post_materiale_id'),
            $forsendelsesModtager,
            $filFormatNavn,
            $meddelelseIndholdData,
            NULL,
            $dokumentParametre,
            NULL,
            NULL,
            $digitalPostParametre
        );

        $brevSPBody = new BrevSPBodyType($kanalValg, $prioritet, $forsendelse);
        $request = new PrintAfsendBrevRequestType($brevSPBody, $invocationContext);

        $certificateLocator = $this->getAzureKeyVaultCertificateLocator(
            $this->config->get('azure_tenant_id'),
            $this->config->get('azure_application_id'),
            $this->config->get('azure_client_secret'),
            $this->config->get('azure_key_vault_name'),
            $this->config->get('azure_key_vault_secret'),
            $this->config->get('azure_key_vault_secret_version')
        );

        $client = new Afsend([
            AbstractSoapClientBase::WSDL_URL => $this->config->get('service_contract'),
            AbstractSoapClientBase::WSDL_CLASSMAP => ClassMap::get(),
            AbstractSoapClientBase::WSDL_LOCAL_CERT => $certificateLocator->getAbsolutePathToCertificate(),
            AbstractSoapClientBase::WSDL_LOCATION => $this->config->get('service_endpoint'),
        ]);

        $response = $client->afsendBrev($request);

        $this->releaseLock();

        if (FALSE === $response) {
            $lastError = $client->getLastError();
            /** @var SoapFault $soapError */
            $soapError = $lastError['Drupal\os2forms_digital_post\Client\ServiceType\Afsend::afsendBrev'];
            // Should maybe log this instead!
            throw new \Exception($soapError->getMessage(), $soapError->getCode());
        }

        return $response->getResultat();
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
     * Afsend digital post person.
     */
    public function afsendDigitalPostPerson(
        string $kanalValg = NULL,
        string $prioritet = NULL,
        string $cprNummerIdentifikator = NULL,
        string $filFormatNavn = NULL,
        string $meddelelseIndholdData = NULL,
        string $titelTekst = NULL
    ): bool {

        if (!$this->acquireLock()) {
            $this->waitLock();
        }

        $invocationContext = new InvocationContextType(
            $this->config->get('service_agreement_uuid'),
            $this->config->get('user_system_uuid'),
            $this->config->get('user_uuid'),
            $this->config->get('service_uuid')
        );

        $slutBrugerIdentitetType = new SlutbrugerIdentitetType($cprNummerIdentifikator);

        $forsendelsesModtager = new ForsendelseModtagerType($slutBrugerIdentitetType);

        $dokumentParametre = new DokumentParametreType($titelTekst);
        $digitalPostParametre = new DigitalPostParametreType(NULL, $this->config->get('digital_post_materiale_id'));

        $forsendelse = new ForsendelseIType(
            $this->generateAfsendelseIdentifikator(),
            NULL,
            $forsendelsesModtager,
            $filFormatNavn,
            $meddelelseIndholdData,
            NULL,
            $dokumentParametre,
            NULL,
            NULL,
            $digitalPostParametre
        );

        $brevSPBody = new BrevSPBodyType($kanalValg, $prioritet, $forsendelse);
        $request = new PrintAfsendBrevRequestType($brevSPBody, $invocationContext);

        $certificateLocator = $this->getAzureKeyVaultCertificateLocator(
            $this->config->get('azure_tenant_id'),
            $this->config->get('azure_application_id'),
            $this->config->get('azure_client_secret'),
            $this->config->get('azure_key_vault_name'),
            $this->config->get('azure_key_vault_secret'),
            $this->config->get('azure_key_vault_secret_version')
        );

        $client = new Afsend([
            AbstractSoapClientBase::WSDL_URL => $this->config->get('service_contract'),
            AbstractSoapClientBase::WSDL_CLASSMAP => ClassMap::get(),
            AbstractSoapClientBase::WSDL_LOCAL_CERT => $certificateLocator->getAbsolutePathToCertificate(),
            AbstractSoapClientBase::WSDL_LOCATION => $this->config->get('service_endpoint'),
        ]);

        $response = $client->afsendBrev($request);

        $this->releaseLock();

        if (FALSE === $response) {
            $lastError = $client->getLastError();
            /** @var SoapFault $soapError */
            $soapError = $lastError['Drupal\os2forms_digital_post\Client\ServiceType\Afsend::afsendBrev'];
            // Should maybe log this instead!
            throw new \Exception($soapError->getMessage(), $soapError->getCode());
        }

        return $response->getResultat();
    }

    /**
     * Generate afsendelse identifikator.
     */
    protected function generateAfsendelseIdentifikator(): string {

        $stateKey = 'os2forms_digital_post_last_letter_counter';

        $lastLetterCounter = $this->state->get($stateKey, 1);

        $nextLetterCounter = $lastLetterCounter + 1;

        $this->state->set($stateKey, $nextLetterCounter);

        $nextLetterNumber = str_pad(strval($nextLetterCounter), 21, '0', STR_PAD_LEFT);

        return $this->config->get('digital_post_system_id')
            . $this->config->get('digital_post_afsender_system')
            . $nextLetterNumber;
    }

    /**
     * Acquire lock.
     */
    protected function acquireLock(): bool {
        return true;
        return $this->lock->acquire($this->lockName);
    }

    /**
     * Release lock.
     */
    protected function releaseLock() {
        return;
        $this->lock->release($this->lockName);
    }

    /**
     * Wait lock.
     */
    protected function waitLock(): bool {
        return true;
        return $this->lock->wait($this->lockName);
    }

}
