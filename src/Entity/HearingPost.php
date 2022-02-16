<?php

namespace App\Entity;

use App\Logging\LoggableEntityInterface;
use App\Repository\HearingPostRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Uid\Uuid;

/**
 * @ORM\Entity(repositoryClass=HearingPostRepository::class)
 * @ORM\EntityListeners({"App\Logging\EntityListener\HearingPostListener"})
 */
class HearingPost implements LoggableEntityInterface
{
    use TimestampableEntity;

    /**
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Hearing::class, inversedBy="hearingPosts")
     * @ORM\JoinColumn(nullable=false)
     */
    private $hearing;

    /**
     * @ORM\ManyToOne(targetEntity=Party::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $recipient;

    /**
     * @ORM\Column(type="text")
     */
    private $content;

    /**
     * @ORM\OneToMany(targetEntity=Document::class, mappedBy="hearingPost")
     */
    private $documents;

    /**
     * @ORM\ManyToOne(targetEntity=MailTemplate::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $template;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $forwardedOn;

    public function __construct()
    {
        $this->id = Uuid::v4();
        $this->documents = new ArrayCollection();
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getHearing(): ?Hearing
    {
        return $this->hearing;
    }

    public function setHearing(?Hearing $hearing): self
    {
        $this->hearing = $hearing;

        return $this;
    }

    public function getRecipient(): ?Party
    {
        return $this->recipient;
    }

    public function setRecipient(Party $recipient): self
    {
        $this->recipient = $recipient;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    /**
     * @return Collection|Document[]
     */
    public function getDocuments(): Collection
    {
        return $this->documents;
    }

    public function addDocument(Document $document): self
    {
        if (!$this->documents->contains($document)) {
            $this->documents[] = $document;
            $document->setHearingPost($this);
        }

        return $this;
    }

    public function removeDocument(Document $document): self
    {
        if ($this->documents->removeElement($document)) {
            // set the owning side to null (unless already changed)
            if ($document->getHearingPost() === $this) {
                $document->setHearingPost(null);
            }
        }

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

    public function getLoggableProperties(): array
    {
        return [
            'recipient',
            'documents',
            'template',
            'forwardedOn',
        ];
    }
}
