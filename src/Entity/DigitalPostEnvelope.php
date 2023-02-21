<?php

namespace App\Entity;

use App\Entity\DigitalPost\Recipient;
use App\Repository\DigitalPostEnvelopeRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Entity(repositoryClass=DigitalPostEnvelopeRepository::class)
 * @ORM\Table(indexes={
 *     @ORM\Index(name="status", fields={"status"})
 * })
 */
class DigitalPostEnvelope
{
    use TimestampableEntity;
    public const STATUS_SENT = 'sent';
    public const STATUS_DELIVERED = 'delivered';
    public const STATUS_FAILED = 'failed';

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $status = self::STATUS_SENT;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $statusMessage;

    /**
     * @ORM\ManyToOne(targetEntity=DigitalPost::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $digitalPost;

    /**
     * @ORM\ManyToOne(targetEntity=Recipient::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $recipient;

    /**
     * The MeMo message Uuid.
     *
     * @ORM\Id
     * @ORM\Column(type="string", length=36, unique=true)
     */
    private string $messageUuid;

    /**
     * The MeMo message (XML).
     *
     * @ORM\Column(type="text")
     */
    private string $message;

    /**
     * The MeMo message receipt (XML).
     *
     * @ORM\Column(type="text")
     */
    private string $receipt;

    /**
     * @ORM\Column(type="json", name="beskedfordeler_messages")
     */
    private array $beskedfordelerMessages = [];

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getStatusMessage(): ?string
    {
        return $this->statusMessage;
    }

    public function setStatusMessage(?string $statusMessage): self
    {
        $this->statusMessage = $statusMessage;

        return $this;
    }

    public function getDigitalPost(): DigitalPost
    {
        return $this->digitalPost;
    }

    public function setDigitalPost($digitalPost): self
    {
        $this->digitalPost = $digitalPost;

        return $this;
    }

    public function getRecipient(): ?Recipient
    {
        return $this->recipient;
    }

    public function setRecipient(?Recipient $recipient): self
    {
        $this->recipient = $recipient;

        return $this;
    }

    public function getMessageUuid(): string
    {
        return $this->messageUuid;
    }

    public function setMessageUuid(string $messageUuid): self
    {
        $this->messageUuid = $messageUuid;

        return $this;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setMessage(string $message): self
    {
        $this->message = $message;

        return $this;
    }

    public function getReceipt(): string
    {
        return $this->receipt;
    }

    /**
     * @param mixed $receipt
     *
     * @return DigitalPostEnvelope
     */
    public function setReceipt(string $receipt): self
    {
        $this->receipt = $receipt;

        return $this;
    }

    public function addBeskedfordelerMessage(string $beskedfordelerMessage): self
    {
        $this->beskedfordelerMessages[] = $beskedfordelerMessage;

        return $this;
    }

    public function getBeskedfordelerMessages(): ?array
    {
        return $this->beskedfordelerMessages;
    }
}
