<?php

namespace App\Entity;

use App\Logging\LoggableEntityInterface;
use App\Repository\DocumentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\IdGenerator\UuidV4Generator;
use Symfony\Component\Uid\UuidV4;

/**
 * @ORM\Entity(repositoryClass=DocumentRepository::class)
 */
class Document implements LoggableEntityInterface
{
    /**
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class=UuidV4Generator::class)
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
     * @ORM\Column(type="string", length=255)
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

    public function __construct()
    {
        $this->caseDocumentRelation = new ArrayCollection();
    }

    public function getId(): ?UuidV4
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

    public function getUploadedBy(): ?string
    {
        return $this->uploadedBy;
    }

    public function setUploadedBy(string $uploadedBy): self
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
}
