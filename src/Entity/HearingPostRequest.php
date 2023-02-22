<?php

namespace App\Entity;

use App\Repository\HearingPostRequestRepository;
use App\Traits\CustomDataTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=HearingPostRequestRepository::class)
 */
class HearingPostRequest extends HearingPost
{
    use CustomDataTrait;

    /**
     * @ORM\ManyToOne(targetEntity=Party::class)
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"mail_template"})
     */
    private ?\App\Entity\Party $recipient = null;

    /**
     * @ORM\ManyToOne(targetEntity=MailTemplate::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private ?\App\Entity\MailTemplate $template = null;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private ?\DateTimeInterface $forwardedOn = null;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"mail_template"})
     */
    private ?string $title = null;

    public function getRecipient(): ?Party
    {
        return $this->recipient;
    }

    public function setRecipient(Party $recipient): self
    {
        $this->recipient = $recipient;

        return $this;
    }

    public function getTemplate(): ?MailTemplate
    {
        return $this->template;
    }

    public function setTemplate(?MailTemplate $template): self
    {
        $this->template = $template;

        return $this;
    }

    public function getForwardedOn(): ?\DateTimeInterface
    {
        return $this->forwardedOn;
    }

    public function setForwardedOn(?\DateTimeInterface $forwardedOn): self
    {
        $this->forwardedOn = $forwardedOn;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getLoggableProperties(): array
    {
        return [
            'title',
            'recipient',
            'template',
            'forwardedOn',
            'document',
            'attachments',
        ];
    }
}
