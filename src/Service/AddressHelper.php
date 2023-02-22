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
use Symfony\Contracts\Translation\TranslatorInterface;
use Throwable;

class AddressHelper implements LoggerAwareInterface, EventSubscriberInterface
{
    use LoggerAwareTrait;

    public function __construct(private readonly PropertyAccessorInterface $propertyAccessor, private readonly EntityManagerInterface $entityManager, private readonly HttpClientInterface $httpClient, private readonly TranslatorInterface $translator)
    {
    }

    /**
     * Validate a case address.
     *
     * @throws AddressException
     */
    public function validateCaseAddress(CaseEntity $case, string $property, bool $flush = true): bool
    {
        $address = $this->getAddress($case, $property);
        $addressData = $this->fetchAddressData((string) $address);

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
            throw $this->createException(sprintf('Property %s.%s must be an instance of %s; is %s', $entity::class, $property, Address::class, $address::class));
        }

        return $address;
    }

    private function createException(string $message, $code = 0, Throwable $previous = null)
    {
        $this->logger->error($message, ['previous' => $previous]);

        return new AddressException($message, $code, $previous);
    }

    /**
     * Get an address object from a stringified address using Adresse datavask.
     *
     * @see https://dawadocs.dataforsyningen.dk/dok/api/adresse#datavask
     *
     * @throws AddressException
     */
    public function fetchAddressData(string $address): array
    {
        try {
            $client = HttpClient::create([
                'base_uri' => 'https://api.dataforsyningen.dk/datavask/adresser',
            ]);

            $response = $client->request('GET', '', [
                'query' => [
                    'betegnelse' => $address,
                ],
            ]);

            $data = $response->toArray();
            // We only accept exact matches
            if (in_array($data['kategori'] ?? null, ['A'])
                && isset($data['resultater'][0]['adresse'])) {
                return $data['resultater'][0]['adresse'];
            }
        } catch (\Throwable $throwable) {
            throw $this->createException($this->translator->trans('Invalid address: {address}', ['address' => $address], 'case'), $throwable->getCode(), $throwable);
        }

        throw $this->createException($this->translator->trans('Invalid address: {address}', ['address' => $address], 'case'));
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

    /**
     * Gets inspection address from case.
     */
    public function getInspectionAddress(CaseEntity $case): Address
    {
        return match ($case::class) {
            \App\Entity\FenceReviewCase::class => $case->getBringerAddress(),
            default => $case->getLeaseAddress(),
        };
    }
}
