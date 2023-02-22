<?php

namespace App\Entity;

use App\Logging\LoggableEntityInterface;
use App\Repository\MailTemplateRepository;
use App\Traits\BlameableEntity;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @Vich\Uploadable
 */
#[ORM\Entity(repositoryClass: MailTemplateRepository::class)]
class MailTemplate implements LoggableEntityInterface, \Stringable
{
    use BlameableEntity;
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    private readonly \Symfony\Component\Uid\UuidV4 $id;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank]
    private ?string $name = null;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank]
    private ?string $description = null;

    #[ORM\Column(type: 'string', length: 255)]
    private string $templateFilename;

    /**
     * @Vich\UploadableField(mapping="mail_templates", fileNameProperty="templateFilename")
     *
     */
    #[Assert\File(maxSize: '4M', mimeTypes: ['application/vnd.openxmlformats-officedocument.wordprocessingml.document'], mimeTypesMessage: 'Please upload a valid Word document (docx).')]
    private ?\Symfony\Component\HttpFoundation\File\File $templateFile = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $type = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $customFields = null;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private bool $isArchived = false;

    public function __construct()
    {
        $this->id = Uuid::v4();
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function setTemplateFile(File $templateFile = null): self
    {
        $this->templateFile = $templateFile;

        if ($templateFile) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = new \DateTime();
        }

        return $this;
    }

    public function getTemplateFile(): ?File
    {
        return $this->templateFile;
    }

    public function setTemplateFilename(?string $templateFilename): self
    {
        $this->templateFilename = $templateFilename;

        return $this;
    }

    public function getTemplateFilename(): ?string
    {
        return $this->templateFilename;
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

    public function getLoggableProperties(): array
    {
        return [
            'name',
            'description',
            'templateFilename',
            'type',
        ];
    }

    public function __toString(): string
    {
        return $this->name;
    }

    public function getCustomFields(): ?string
    {
        return $this->customFields;
    }

    public function setCustomFields(?string $customFields): self
    {
        $this->customFields = $customFields;

        return $this;
    }

    public function getIsArchived(): bool
    {
        return $this->isArchived;
    }

    public function setIsArchived(bool $isArchived): self
    {
        $this->isArchived = $isArchived;

        return $this;
    }
}
