<?php

namespace App\Entity;

use App\Repository\MailTemplateMacroRepository;
use App\Traits\BlameableEntity;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=MailTemplateMacroRepository::class)
 */
class MailTemplateMacro
{
    use BlameableEntity;
    use TimestampableEntity;

    /**
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Regex(pattern="/^[[:alnum:]._-]+$/", message="Only letters, digits, dots, dashed and underscores allowed")
     */
    private $macro;

    /**
     * @ORM\Column(type="text")
     */
    private $content;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $templateTypes;

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

    public function getMacro(): ?string
    {
        return $this->macro;
    }

    public function setMacro(string $macro): self
    {
        $this->macro = $macro;

        return $this;
    }

    public function setContent(?string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getContent(): string
    {
        return $this->content ?? '';
    }

    public function getTemplateTypes(): array
    {
        return $this->templateTypes;
    }

    /**
     * @param mixed $templateTypes
     */
    public function setTemplateTypes(array $templateTypes): self
    {
        $this->templateTypes = $templateTypes;

        return $this;
    }
}
