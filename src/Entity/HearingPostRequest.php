<?php

namespace App\Entity;

use App\Repository\HearingPostRequestRepository;
use App\Traits\CustomDataTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=HearingPostRequestRepository::class)
 */
class HearingPostRequest extends HearingPost
{
    use CustomDataTrait;

    /**
     * @ORM\ManyToOne(targetEntity=MailTemplate::class)
     *
     * @ORM\JoinColumn(nullable=false)
     */
    private $template;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $forwardedOn;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @Groups({"mail_template"})
     */
    private $title;

    /**
     * @ORM\OneToMany(targetEntity=HearingRecipient::class, mappedBy="hearingPostRequest", orphanRemoval=true)
     */
    private $hearingRecipients;

    /**
     * @ORM\OneToOne(targetEntity=HearingBriefing::class, inversedBy="hearingPostRequest", cascade={"persist", "remove"})
     */
    private $briefing;

    /**
     * @ORM\Column(type="boolean", options={"default":"0"})
     */
    private $shouldSendBriefing = false;

    public function __construct()
    {
        parent::__construct();
        $this->hearingRecipients = new ArrayCollection();
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
            'template',
            'forwardedOn',
            'document',
            'attachments',
        ];
    }

    /**
     * @return Collection<int, HearingRecipient>
     */
    public function getHearingRecipients(): Collection
    {
        return $this->hearingRecipients;
    }

    public function addHearingRecipient(HearingRecipient $hearingRecipient): self
    {
        if (!$this->hearingRecipients->contains($hearingRecipient)) {
            $this->hearingRecipients[] = $hearingRecipient;
            $hearingRecipient->setHearingPostRequest($this);
        }

        return $this;
    }

    public function removeHearingRecipient(HearingRecipient $hearingRecipient): self
    {
        if ($this->hearingRecipients->removeElement($hearingRecipient)) {
            // set the owning side to null (unless already changed)
            if ($hearingRecipient->getHearingPostRequest() === $this) {
                $hearingRecipient->setHearingPostRequest(null);
            }
        }

        return $this;
    }

    public function getBriefing(): ?HearingBriefing
    {
        return $this->briefing;
    }

    public function setBriefing(?HearingBriefing $briefing): self
    {
        $this->briefing = $briefing;

        return $this;
    }

    public function shouldSendBriefing(): bool
    {
        return $this->shouldSendBriefing;
    }

    public function setShouldSendBriefing(bool $shouldSendBriefing): self
    {
        $this->shouldSendBriefing = $shouldSendBriefing;

        return $this;
    }
}
