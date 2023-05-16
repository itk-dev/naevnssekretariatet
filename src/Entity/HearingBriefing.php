<?php

namespace App\Entity;

use App\Repository\HearingBriefingRepository;
use App\Traits\CustomDataTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Uid\Uuid;

/**
 * @ORM\Entity(repositoryClass=HearingBriefingRepository::class)
 */
class HearingBriefing
{
    use TimestampableEntity;
    use CustomDataTrait;

    /**
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity=HearingPostRequest::class, mappedBy="briefing", cascade={"persist", "remove"})
     */
    private $hearingPostRequest;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $title;

    /**
     * @ORM\ManyToOne(targetEntity=MailTemplate::class)
     */
    private $template;

    /**
     * @ORM\OneToMany(targetEntity=HearingBriefingRecipient::class, mappedBy="hearingBriefing")
     */
    private $hearingBriefingRecipients;

    public function __construct()
    {
        $this->id = Uuid::v4();
        $this->hearingBriefingRecipients = new ArrayCollection();
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getHearingPostRequest(): ?HearingPostRequest
    {
        return $this->hearingPostRequest;
    }

    public function setHearingPostRequest(?HearingPostRequest $hearingPostRequest): self
    {
        // unset the owning side of the relation if necessary
        if (null === $hearingPostRequest && null !== $this->hearingPostRequest) {
            $this->hearingPostRequest->setBriefing(null);
        }

        // set the owning side of the relation if necessary
        if (null !== $hearingPostRequest && $hearingPostRequest->getBriefing() !== $this) {
            $hearingPostRequest->setBriefing($this);
        }

        $this->hearingPostRequest = $hearingPostRequest;

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

    /**
     * @return Collection<int, HearingBriefingRecipient>
     */
    public function getHearingBriefingRecipients(): Collection
    {
        return $this->hearingBriefingRecipients;
    }

    public function addHearingBriefingRecipient(HearingBriefingRecipient $hearingBriefingRecipient): self
    {
        if (!$this->hearingBriefingRecipients->contains($hearingBriefingRecipient)) {
            $this->hearingBriefingRecipients[] = $hearingBriefingRecipient;
            $hearingBriefingRecipient->setHearingBriefing($this);
        }

        return $this;
    }

    public function removeHearingBriefingRecipient(HearingBriefingRecipient $hearingBriefingRecipient): self
    {
        if ($this->hearingBriefingRecipients->removeElement($hearingBriefingRecipient)) {
            // set the owning side to null (unless already changed)
            if ($hearingBriefingRecipient->getHearingBriefing() === $this) {
                $hearingBriefingRecipient->setHearingBriefing(null);
            }
        }

        return $this;
    }
}
