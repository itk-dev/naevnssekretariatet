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
    public const STATUS_SENT = 'sent';
    public const STATUS_ERROR = 'error';

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

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     */
    private $sentAt;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $data = [];

    public function __construct()
    {
        parent::__construct();
        $this
            ->setType('Digital post')
        ;
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

    public function getSentAt(): ?\DateTimeInterface
    {
        return $this->sentAt;
    }

    public function setSentAt(?\DateTimeInterface $sentAt): self
    {
        $this->sentAt = $sentAt;

        return $this;
    }

    public function getData(): array
    {
        return $this->data ?? [];
    }

    public function setData(?array $data): self
    {
        $this->data = $data;

        return $this;
    }

    public function addData(array $data): self
    {
        return $this->setData(array_merge($this->getData(), $data));
    }
}
