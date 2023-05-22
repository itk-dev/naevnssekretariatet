<?php

namespace App\Entity;

use App\Repository\HearingPostResponseRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=HearingPostResponseRepository::class)
 */
class HearingPostResponse extends HearingPost
{
    /**
     * @ORM\ManyToOne(targetEntity=Party::class)
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"mail_template"})
     */
    private $sender;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $approvedOn;

    /**
     * @ORM\Column(type="text", nullable=false)
     * @Groups({"mail_template"})
     */
    private $response;

    /**
     * @ORM\ManyToOne(targetEntity=Document::class)
     */
    private $document;

    public function getSender(): ?Party
    {
        return $this->sender;
    }

    public function setSender(Party $sender): self
    {
        $this->sender = $sender;

        return $this;
    }

    public function getApprovedOn(): ?\DateTimeInterface
    {
        return $this->approvedOn;
    }

    public function setApprovedOn(?\DateTimeInterface $approvedOn): self
    {
        $this->approvedOn = $approvedOn;

        return $this;
    }

    public function getLoggableProperties(): array
    {
        return [
            'sender',
            'approvedOn',
            'document',
            'attachments',
        ];
    }

    public function getResponse(): ?string
    {
        return $this->response;
    }

    public function setResponse(?string $response): self
    {
        $this->response = $response;

        return $this;
    }

    public function getDocument(): ?Document
    {
        return $this->document;
    }

    public function setDocument(?Document $document): self
    {
        $this->document = $document;

        return $this;
    }
}
