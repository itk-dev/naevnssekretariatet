<?php

namespace App\Entity;

use App\Logging\LoggableEntityInterface;
use App\Repository\HearingRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Uid\Uuid;

/**
 * @ORM\Entity(repositoryClass=HearingRepository::class)
 */
class Hearing implements LoggableEntityInterface
{
    use TimestampableEntity;

    /**
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     */
    private $id;

    /**
     * @ORM\OneToMany(targetEntity=HearingPost::class, mappedBy="hearing")
     */
    private $hearingPosts;

    /**
     * @ORM\OneToOne(targetEntity=CaseEntity::class, mappedBy="hearing", cascade={"persist", "remove"})
     */
    private $caseEntity;

    /**
     * @ORM\Column(type="boolean", options={"default":"0"})
     */
    private $complainantHasNoMoreToAdd = false;

    /**
     * @ORM\Column(type="boolean", options={"default":"0"})
     */
    private $counterpartHasNoMoreToAdd = false;

    /**
     * @ORM\Column(type="boolean", options={"default":"0"})
     */
    private $hasNewHearingPost = false;

    /**
     * @ORM\Column(type="boolean", options={"default":"0"})
     */
    private $hasBeenStarted = false;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $startDate;

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

    public function getLoggableProperties(): array
    {
        return [];
    }

    public function getComplainantHasNoMoreToAdd(): ?bool
    {
        return $this->complainantHasNoMoreToAdd;
    }

    public function setComplainantHasNoMoreToAdd(bool $complainantHasNoMoreToAdd): self
    {
        $this->complainantHasNoMoreToAdd = $complainantHasNoMoreToAdd;

        return $this;
    }

    public function getCounterpartHasNoMoreToAdd(): ?bool
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

    public function getHasBeenStarted(): ?bool
    {
        return $this->hasBeenStarted;
    }

    public function setHasBeenStarted(bool $hasBeenStarted): self
    {
        $this->hasBeenStarted = $hasBeenStarted;

        return $this;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(?\DateTimeInterface $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }
}
