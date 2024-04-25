<?php

namespace App\Entity;

use App\Logging\LoggableEntityInterface;
use App\Repository\HearingRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;

/**
 * @ORM\Entity(repositoryClass=HearingRepository::class)
 *
 * @ORM\EntityListeners({"App\Logging\EntityListener\HearingListener"})
 */
class Hearing implements LoggableEntityInterface
{
    use TimestampableEntity;

    /**
     * @ORM\Id
     *
     * @ORM\Column(type="uuid", unique=true)
     */
    private $id;

    /**
     * @ORM\OneToMany(targetEntity=HearingPost::class, mappedBy="hearing")
     */
    private $hearingPosts;

    /**
     * @ORM\OneToOne(targetEntity=CaseEntity::class, mappedBy="hearing", cascade={"persist", "remove"})
     *
     * @Groups({"mail_template"})
     */
    private $caseEntity;

    /**
     * @ORM\Column(type="boolean", options={"default":"0"})
     */
    private $partyHasNoMoreToAdd = false;

    /**
     * @ORM\Column(type="boolean", options={"default":"0"})
     */
    private $counterpartHasNoMoreToAdd = false;

    /**
     * @ORM\Column(type="boolean", options={"default":"0"})
     */
    private $hasNewHearingPost = false;

    /**
     * @ORM\Column(type="date", nullable=true)
     *
     * @Groups({"mail_template"})
     */
    private $startedOn;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $finishedOn;

    public function __construct()
    {
        $this->id = Uuid::v4();
        $this->hearingPosts = new ArrayCollection();
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    /**
     * @return Collection|HearingPost[]
     */
    public function getHearingPosts(): Collection
    {
        return $this->hearingPosts;
    }

    public function addHearingPost(HearingPost $hearingPost): self
    {
        if (!$this->hearingPosts->contains($hearingPost)) {
            $this->hearingPosts[] = $hearingPost;
            $hearingPost->setHearing($this);
        }

        return $this;
    }

    public function removeHearingPost(HearingPost $hearingPost): self
    {
        if ($this->hearingPosts->removeElement($hearingPost)) {
            // set the owning side to null (unless already changed)
            if ($hearingPost->getHearing() === $this) {
                $hearingPost->setHearing(null);
            }
        }

        return $this;
    }

    public function getCaseEntity(): ?CaseEntity
    {
        return $this->caseEntity;
    }

    public function setCaseEntity(?CaseEntity $caseEntity): self
    {
        // unset the owning side of the relation if necessary
        if (null === $caseEntity && null !== $this->caseEntity) {
            $this->caseEntity->setHearing(null);
        }

        // set the owning side of the relation if necessary
        if (null !== $caseEntity && $caseEntity->getHearing() !== $this) {
            $caseEntity->setHearing($this);
        }

        $this->caseEntity = $caseEntity;

        return $this;
    }

    public function getPartyHasNoMoreToAdd(): bool
    {
        return $this->partyHasNoMoreToAdd;
    }

    public function setPartyHasNoMoreToAdd(bool $partyHasNoMoreToAdd): self
    {
        $this->partyHasNoMoreToAdd = $partyHasNoMoreToAdd;

        return $this;
    }

    public function getCounterpartHasNoMoreToAdd(): bool
    {
        return $this->counterpartHasNoMoreToAdd;
    }

    public function setCounterpartHasNoMoreToAdd(bool $counterpartHasNoMoreToAdd): self
    {
        $this->counterpartHasNoMoreToAdd = $counterpartHasNoMoreToAdd;

        return $this;
    }

    public function getHasNewHearingPost(): ?bool
    {
        return $this->hasNewHearingPost;
    }

    public function setHasNewHearingPost(bool $hasNewHearingPost): self
    {
        $this->hasNewHearingPost = $hasNewHearingPost;

        return $this;
    }

    public function getStartedOn(): ?\DateTimeInterface
    {
        return $this->startedOn;
    }

    public function setStartedOn(?\DateTimeInterface $startedOn): self
    {
        $this->startedOn = $startedOn;

        return $this;
    }

    public function getFinishedOn(): ?\DateTimeInterface
    {
        return $this->finishedOn;
    }

    public function setFinishedOn(?\DateTimeInterface $finishedOn): self
    {
        $this->finishedOn = $finishedOn;

        return $this;
    }

    public function getLoggableProperties(): array
    {
        return [
            'partyHasNoMoreToAdd',
            'counterpartHasNoMoreToAdd',
            'hasNewHearingPost',
            'startedOn',
            'finishedOn',
        ];
    }
}
