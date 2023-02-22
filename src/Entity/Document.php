<?php

namespace App\Entity;

use App\Logging\LoggableEntityInterface;
use App\Repository\DocumentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: DocumentRepository::class)]
class Document implements LoggableEntityInterface, \Stringable
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    private readonly \Symfony\Component\Uid\UuidV4 $id;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $documentName = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $type = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?\App\Entity\User $uploadedBy = null;

    /**
     * @Gedmo\Timestampable(on="create")
     */
    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $uploadedAt = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $filename = null;

    #[ORM\OneToMany(targetEntity: 'CaseDocumentRelation', mappedBy: 'document')]
    private \Doctrine\Common\Collections\ArrayCollection|array $caseDocumentRelations;

    #[ORM\ManyToMany(targetEntity: AgendaCaseItem::class, mappedBy: 'documents')]
    private \Doctrine\Common\Collections\ArrayCollection|array $agendaCaseItems;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $originalFileName = null;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private bool $isCreatedManually = false;

    public function __construct()
    {
        $this->id = Uuid::v4();
        $this->caseDocumentRelations = new ArrayCollection();
        $this->agendaCaseItems = new ArrayCollection();
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getDocumentName(): ?string
    {
        return $this->documentName;
    }

    public function setDocumentName(string $documentName): self
    {
        $this->documentName = $documentName;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getUploadedBy(): ?User
    {
        return $this->uploadedBy;
    }

    public function setUploadedBy(?User $uploadedBy): self
    {
        $this->uploadedBy = $uploadedBy;

        return $this;
    }

    public function getUploadedAt(): ?\DateTimeInterface
    {
        return $this->uploadedAt;
    }

    public function setUploadedAt(\DateTimeInterface $uploadedAt): self
    {
        $this->uploadedAt = $uploadedAt;

        return $this;
    }

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function setFilename(string $filename): self
    {
        $this->filename = $filename;

        return $this;
    }

    /**
     * @return Collection|CaseDocumentRelation[]
     */
    public function getCaseDocumentRelations(): Collection
    {
        return $this->caseDocumentRelations;
    }

    public function addCaseDocumentRelation(CaseDocumentRelation $caseDocumentRelation): self
    {
        if (!$this->caseDocumentRelations->contains($caseDocumentRelation)) {
            $this->caseDocumentRelations[] = $caseDocumentRelation;
        }

        return $this;
    }

    public function removeCaseDocumentRelation(CaseDocumentRelation $caseDocumentRelation): self
    {
        $this->caseDocumentRelations->removeElement($caseDocumentRelation);

        return $this;
    }

    public function getLoggableProperties(): array
    {
        return [
            'id',
            'documentName',
            'type',
        ];
    }

    /**
     * @return Collection|AgendaCaseItem[]
     */
    public function getAgendaCaseItems(): Collection
    {
        return $this->agendaCaseItems;
    }

    public function addAgendaCaseItem(AgendaCaseItem $agendaCaseItem): self
    {
        if (!$this->agendaCaseItems->contains($agendaCaseItem)) {
            $this->agendaCaseItems[] = $agendaCaseItem;
            $agendaCaseItem->addDocument($this);
        }

        return $this;
    }

    public function removeAgendaCaseItem(AgendaCaseItem $agendaCaseItem): self
    {
        if ($this->agendaCaseItems->removeElement($agendaCaseItem)) {
            $agendaCaseItem->removeDocument($this);
        }

        return $this;
    }

    public function __toString(): string
    {
        return (string) $this->documentName;
    }

    public function getOriginalFileName(): ?string
    {
        return $this->originalFileName;
    }

    public function setOriginalFileName(string $originalFileName): self
    {
        $this->originalFileName = $originalFileName;

        return $this;
    }

    public function isCreatedManually(): ?bool
    {
        return $this->isCreatedManually;
    }

    public function setIsCreatedManually(bool $isCreatedManually): self
    {
        $this->isCreatedManually = $isCreatedManually;

        return $this;
    }
}
