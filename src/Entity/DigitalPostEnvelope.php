<?php

namespace App\Entity;

use App\Entity\DigitalPost\Recipient;
use App\Repository\DigitalPostEnvelopeRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Uid\Uuid;

/**
 * @ORM\Entity(repositoryClass=DigitalPostEnvelopeRepository::class)
 * @ORM\Table(indexes={
 *     @ORM\Index(name="status", fields={"status"})
 * })
 */
class DigitalPostEnvelope
{
    use TimestampableEntity;

    public const STATUS_CREATED = 'created';
    public const STATUS_SENT = 'sent';
    public const STATUS_DELIVERED = 'delivered';
    public const STATUS_FAILED = 'failed';

    /**
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     */
    private ?Uuid $id;

    /**
     * @ORM\Column(type="uuid", nullable=true)
     */
    private ?Uuid $transactionId;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $status = self::STATUS_CREATED;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $statusMessage;

    /**
     * Serialized throwable.
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $throwable;

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
     * The MeMo message uuid.
     *
     * @ORM\Column(type="string", length=36, nullable=true)
     */
    private ?string $meMoMessageUuid;

    /**
     * The MeMo message (XML).
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $meMoMessage;

    /**
     * The forsendelse uuid.
     *
     * @ORM\Column(type="string", length=36, nullable=true)
     */
    private ?string $forsendelseUuid;

    /**
     * The forsendelse (XML).
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $forsendelse;

    /**
     * The MeMo message receipt (XML).
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $receipt;

    /**
     * @ORM\Column(type="json", name="beskedfordeler_messages")
     */
    private array $beskedfordelerMessages = [];

    public function __construct()
    {
        $this->id = Uuid::v4();
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getTransactionId(): ?Uuid
    {
        return $this->transactionId;
    }

    public function setTransactionId(Uuid $transactionId): self
    {
        $this->transactionId = $transactionId;

        return $this;
    }

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
        if (null !== $statusMessage) {
            $statusMessage = mb_substr($statusMessage, 0, 255);
        }
        $this->statusMessage = $statusMessage;

        return $this;
    }

    /**
     * Get serialized throwable if any.
     */
    public function getThrowable(): ?string
    {
        return $this->throwable;
    }

    public function setThrowable(\Throwable $throwable): self
    {
        $this->setStatusMessage($throwable->getMessage());
        try {
            $this->throwable = serialize($throwable);
        } catch (\Throwable $throwable) {
            try {
                $this->throwable = serialize(['throwable' => ['message' => $throwable->getMessage()]]);
            } catch (\Throwable) {
                // Ignore any exceptions.
            }
        }

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

    public function getMeMoMessageUuid(): ?string
    {
        return $this->meMoMessageUuid;
    }

    public function setMeMoMessageUuid(string $meMoMessageUuid): self
    {
        $this->meMoMessageUuid = $meMoMessageUuid;

        return $this;
    }

    public function getMeMoMessage(): ?string
    {
        return $this->meMoMessage;
    }

    public function setMeMoMessage(string $message): self
    {
        $this->meMoMessage = $message;

        return $this;
    }

    public function getForsendelseUuid(): ?string
    {
        return $this->forsendelseUuid;
    }

    public function setForsendelseUuid(string $forsendelseUuid): self
    {
        $this->forsendelseUuid = $forsendelseUuid;

        return $this;
    }

    public function getForsendelse(): ?string
    {
        return $this->forsendelse;
    }

    public function setForsendelse(string $forsendelse): self
    {
        $this->forsendelse = $forsendelse;

        return $this;
    }

    public function getReceipt(): ?string
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

    public function removeBeskedfordelerMessage(string $beskedfordelerMessage): self
    {
        foreach (array_keys($this->beskedfordelerMessages, $beskedfordelerMessage, true) as $key) {
            unset($this->beskedfordelerMessages[$key]);
        }
        $this->beskedfordelerMessages = array_keys($this->beskedfordelerMessages);

        return $this;
    }

    public function getBeskedfordelerMessages(): ?array
    {
        return $this->beskedfordelerMessages;
    }
}
