<?php

namespace App\Entity;

use App\Repository\InspectionLetterRepository;
use App\Traits\CustomDataTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Uid\Uuid;

/**
 * @ORM\Entity(repositoryClass=InspectionLetterRepository::class)
 */
class InspectionLetter implements \Stringable
{
    use CustomDataTrait;
    use TimestampableEntity;

    /**
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     */
    private \Symfony\Component\Uid\UuidV4 $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private ?string $title = null;

    /**
     * @ORM\ManyToOne(targetEntity=MailTemplate::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private ?\App\Entity\MailTemplate $template = null;

    /**
     * @ORM\ManyToOne(targetEntity=Document::class)
     */
    private ?\App\Entity\Document $document = null;

    /**
     * @ORM\ManyToOne(targetEntity=AgendaCaseItem::class, inversedBy="inspectionLetters")
     * @ORM\JoinColumn(nullable=false)
     */
    private ?\App\Entity\AgendaCaseItem $agendaCaseItem = null;

    /**
     * @ORM\ManyToMany(targetEntity=Party::class)
     */
    private \Doctrine\Common\Collections\ArrayCollection|array $recipients;

    public function __toString(): string
    {
        return sprintf('%s (%s)', $this->title, $this->id);
    }

    public function __construct()
    {
        $this->id = Uuid::v4();
        $this->recipients = new ArrayCollection();
    }

    public function getId(): ?Uuid
    {
        return $this->id;
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

    public function getTemplate(): ?MailTemplate
    {
        return $this->template;
    }

    public function setTemplate(?MailTemplate $template): self
    {
        $this->template = $template;

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

    public function getAgendaCaseItem(): ?AgendaCaseItem
    {
        return $this->agendaCaseItem;
    }

    public function setAgendaCaseItem(?AgendaCaseItem $agendaCaseItem): self
    {
        $this->agendaCaseItem = $agendaCaseItem;

        return $this;
    }

    /**
     * @return Collection|Party[]
     */
    public function getRecipients(): Collection
    {
        return $this->recipients;
    }

    public function addRecipient(Party $recipient): self
    {
        if (!$this->recipients->contains($recipient)) {
            $this->recipients[] = $recipient;
        }

        return $this;
    }

    public function removeRecipient(Party $recipient): self
    {
        $this->recipients->removeElement($recipient);

        return $this;
    }
}
