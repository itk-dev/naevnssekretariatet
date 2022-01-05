<?php

namespace App\Service;

use App\Entity\CaseEntity;
use App\Entity\Embeddable\Address;
use App\Exception\AddressException;
use Doctrine\Bundle\DoctrineBundle\EventSubscriber\EventSubscriberInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;

class AddressHelper implements LoggerAwareInterface, EventSubscriberInterface
{
    use LoggerAwareTrait;

    private PropertyAccessorInterface $propertyAccessor;
    private EntityManagerInterface $entityManager;
    private HttpClientInterface $httpClient;

    public function __construct(PropertyAccessorInterface $propertyAccessor, EntityManagerInterface $entityManager, HttpClientInterface $httpClient, array $bbrHelperOptions)
    {
        $this->propertyAccessor = $propertyAccessor;
        $this->entityManager = $entityManager;
        $this->httpClient = $httpClient;
    }

    /**
     * Validate an address.
     *
     * @throws AddressException
     */
    public function validateAddress(CaseEntity $case, string $property, bool $flush = true): bool
    {
        $address = $this->getAddress($case, $property);
        $addressData = $this->fetchAddressData($address);
        $address->setValidatedAt(new \DateTimeImmutable());

        if ($flush) {
            $this->entityManager->persist($case);
            $this->entityManager->flush();
        }

        return !empty($addressData);
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

        return new AddressException($message, $code, $previous);
    }

    /**
     * Fetch address data using https://dawadocs.dataforsyningen.dk/dok/api/adresse#s%C3%B8gning.
     *
     * @throws AddressException
     */
    private function fetchAddressData(Address $address): array
    {
        try {
            $client = HttpClient::create([
                'base_uri' => 'https://api.dataforsyningen.dk/adresser',
            ]);

            $response = $client->request('GET', '', [
                'query' => [
                    'postnr' => $address->getPostalCode(),
                    'vejnavn' => $address->getStreet(),
                    'husnr' => $address->getNumber(),
                    'etage' => $address->getFloor(),
                    'dør' => $address->getSide(),
                ],
            ]);

            $data = $response->toArray();

            if (1 === count($data)) {
                return reset($data);
            }

            if (count($data) > 1) {
                throw $this->createException(sprintf('Ambiguous address: %s', (string) $address));
            }
        } catch (\Throwable $throwable) {
            throw $this->createException(sprintf('Invalid address: %s', (string) $address), $throwable->getCode(), $throwable);
        }

        throw $this->createException(sprintf('Invalid address: %s', (string) $address));
    }

    /**
     * Set Address.validatedAt to null when changing embedded Address entities.
     */
    public function preUpdate(PreUpdateEventArgs $args)
    {
        $object = $args->getObject();
        $changeSet = $args->getEntityChangeSet();
        foreach ($changeSet as $propertyPath => $value) {
            // Check for embedded property path, i.e. a property path followed
            // by a dot and a name.
            if (preg_match('/^(.+)\.([^.]+)$/', $propertyPath, $matches)) {
                [, $embeddedPath, $embeddedProperty] = $matches;
                if ($this->propertyAccessor->isReadable($object, $embeddedPath)) {
                    $embedded = $this->propertyAccessor->getValue($object, $embeddedPath);
                    // Only set validatedAt if not already being set in the change set.
                    if ($embedded instanceof Address && 'validatedAt' !== $embeddedProperty) {
                        $embedded->setValidatedAt(null);
                        break;
                    }
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
