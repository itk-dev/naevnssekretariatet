<?php

namespace App\Entity;

use App\Logging\LoggableEntityInterface;
use App\Repository\DocumentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Uid\Uuid;

/**
 * @ORM\Entity(repositoryClass=DocumentRepository::class)
 */
class Document implements LoggableEntityInterface
{
    /**
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $documentName;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $type;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $uploadedBy;

    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    private $uploadedAt;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $filename;

    /**
     * @ORM\OneToMany(targetEntity="CaseDocumentRelation", mappedBy="document")
     */
    private $caseDocumentRelation;

    /**
     * @ORM\ManyToMany(targetEntity=AgendaCaseItem::class, mappedBy="documents")
     */
    private $agendaCaseItems;

    /**
     * @ORM\ManyToOne(targetEntity=HearingPost::class, inversedBy="documents")
     */
    private $hearingPost;

    public function __construct()
    {
        $this->id = Uuid::v4();
        $this->caseDocumentRelation = new ArrayCollection();
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
    public function getCaseDocumentRelation(): Collection
    {
        return $this->caseDocumentRelation;
    }

    public function addCaseDocumentRelation(CaseDocumentRelation $caseDocumentRelation): self
    {
        if (!$this->caseDocumentRelation->contains($caseDocumentRelation)) {
            $this->caseDocumentRelation[] = $caseDocumentRelation;
        }

        return $this;
    }

    public function removeCaseDocumentRelation(CaseDocumentRelation $caseDocumentRelation): self
    {
        $this->caseDocumentRelation->removeElement($caseDocumentRelation);

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

    public function __toString()
    {
        return $this->documentName;
    }

    public function getHearingPost(): ?HearingPost
    {
        return $this->hearingPost;
    }

    public function setHearingPost(?HearingPost $hearingPost): self
    {
        $this->hearingPost = $hearingPost;

        return $this;
    }
}
