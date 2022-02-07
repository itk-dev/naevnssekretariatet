<?php

namespace App\Entity;

use App\Entity\Embeddable\Address;
use App\Repository\DigitalPostDocumentRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\String\ByteString;

/**
 * @ORM\Entity(repositoryClass=DigitalPostDocumentRepository::class)
 * @ORM\Table(name="document_digital_post")
 */
class DigitalPostDocument extends Document
{
    /**
     * @ORM\Column(type="string", unique=true)
     */
    private string $serialNumber;

    /**
     * @ORM\Column(type="string", length=10)
     */
    private string $recipientCpr;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $recipientName;

    /**
     * @ORM\Embedded(class="App\Entity\Embeddable\Address")
     */
    private Address $recipientAddress;

    /**
     * @ORM\Column(type="string", length=32, nullable=true)
     */
    private string $status;

    public function __construct()
    {
        parent::__construct();
        $this
            ->setType('Digital post')
            ->setSerialNumber(ByteString::fromRandom(21)->toString())
        ;
    }

    public function getSerialNumber(): string
    {
        return $this->serialNumber;
    }

    /**
     * @return DigitalPostDocument
     */
    public function setSerialNumber(string $serialNumber): self
    {
        if (strlen($serialNumber) > 21) {
            throw new \RuntimeException(sprintf('The digital post serial contains more the 21 characters: %s', $serialNumber));
        }

        $this->serialNumber = $serialNumber;

        return $this;
    }

    public function getRecipientCpr(): string
    {
        return $this->recipientCpr;
    }

    /**
     * @return DigitalPostDocument
     */
    public function setRecipientCpr(string $recipientCpr): self
    {
        $this->recipientCpr = $recipientCpr;

        return $this;
    }

    public function getRecipientName(): string
    {
        return $this->recipientName;
    }

    /**
     * @return DigitalPostDocument
     */
    public function setRecipientName(string $recipientName): self
    {
        $this->recipientName = $recipientName;

        return $this;
    }

    public function getRecipientAddress(): Address
    {
        return $this->recipientAddress;
    }

    /**
     * @return DigitalPostDocument
     */
    public function setRecipientAddress(Address $recipientAddress): self
    {
        $this->recipientAddress = $recipientAddress;

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @return DigitalPostDocument
     */
    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }
}
