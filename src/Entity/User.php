<?php

namespace App\Entity;

use App\Logging\LoggableEntityInterface;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 *
 * @Vich\Uploadable
 */
class User implements UserInterface, LoggableEntityInterface, \Serializable
{
    use TimestampableEntity;

    /**
     * @ORM\Id
     *
     * @ORM\Column(type="uuid", unique=true)
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $email;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $loginToken;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @Groups({"mail_template"})
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity=Municipality::class)
     */
    private $favoriteMunicipality;

    /**
     * @ORM\OneToMany(targetEntity=CaseEntity::class, mappedBy="assignedTo")
     */
    private $assignedCases;

    /**
     * @ORM\OneToMany(targetEntity=Reminder::class, mappedBy="createdBy")
     */
    private $reminders;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     *
     * @Groups({"mail_template"})
     */
    private $initials;

    /**
     * @ORM\OneToOne(targetEntity=BoardMember::class, cascade={"persist", "remove"})
     */
    private $boardMember;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $shortcuts;

    /**
     * @Vich\UploadableField(mapping="user_signatures", fileNameProperty="signatureFilename")
     *
     * @Assert\File(
     *     maxSize = "1M",
     *     mimeTypes = {"image/jpeg", "image/png"},
     *     mimeTypesMessage = "Please upload a valid image (jpg or png (preferred))."
     * )
     */
    private ?File $signatureFile = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $signatureFilename = null;

    public function __construct()
    {
        $this->id = Uuid::v4();
        $this->assignedCases = new ArrayCollection();
        $this->reminders = new ArrayCollection();
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getUserIdentifier(): string
    {
        return $this->getUsername();
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * This method is not needed for apps that do not check user passwords.
     *
     * @see UserInterface
     */
    public function getPassword(): ?string
    {
        return null;
    }

    /**
     * This method is not needed for apps that do not check user passwords.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getLoginToken(): ?string
    {
        return $this->loginToken;
    }

    public function setLoginToken($loginToken): self
    {
        $this->loginToken = $loginToken;

        return $this;
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

    public function getFavoriteMunicipality(): ?Municipality
    {
        return $this->favoriteMunicipality;
    }

    public function setFavoriteMunicipality(?Municipality $favoriteMunicipality): self
    {
        $this->favoriteMunicipality = $favoriteMunicipality;

        return $this;
    }

    public function __toString()
    {
        return $this->name;
    }

    /**
     * @return Collection|CaseEntity[]
     */
    public function getAssignedCases(): Collection
    {
        return $this->assignedCases;
    }

    public function addAssignedCase(CaseEntity $assignedCase): self
    {
        if (!$this->assignedCases->contains($assignedCase)) {
            $this->assignedCases[] = $assignedCase;
            $assignedCase->setAssignedTo($this);
        }

        return $this;
    }

    public function removeAssignedCase(CaseEntity $assignedCase): self
    {
        if ($this->assignedCases->removeElement($assignedCase)) {
            // set the owning side to null (unless already changed)
            if ($assignedCase->getAssignedTo() === $this) {
                $assignedCase->setAssignedTo(null);
            }
        }

        return $this;
    }

    public function getLoggableProperties(): array
    {
        return [
            'id',
            'name',
            'email',
        ];
    }

    /**
     * @return Collection|Reminder[]
     */
    public function getReminders(): Collection
    {
        return $this->reminders;
    }

    public function addReminder(Reminder $reminder): self
    {
        if (!$this->reminders->contains($reminder)) {
            $this->reminders[] = $reminder;
            $reminder->setCreatedBy($this);
        }

        return $this;
    }

    public function removeReminder(Reminder $reminder): self
    {
        if ($this->reminders->removeElement($reminder)) {
            // set the owning side to null (unless already changed)
            if ($reminder->getCreatedBy() === $this) {
                $reminder->setCreatedBy(null);
            }
        }

        return $this;
    }

    public function getInitials(): ?string
    {
        return $this->initials;
    }

    public function setInitials(?string $initials): self
    {
        $this->initials = $initials;

        return $this;
    }

    public function getBoardMember(): ?BoardMember
    {
        return $this->boardMember;
    }

    public function setBoardMember(?BoardMember $boardMember): self
    {
        $this->boardMember = $boardMember;

        return $this;
    }

    public function getShortcuts(): ?string
    {
        return $this->shortcuts;
    }

    public function setShortcuts(?string $shortcuts): self
    {
        $this->shortcuts = $shortcuts;

        return $this;
    }

    public function getSignatureFile(): ?File
    {
        return $this->signatureFile;
    }

    public function setSignatureFile(?File $signatureFile = null): self
    {
        $this->signatureFile = $signatureFile;

        if ($signatureFile) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = new \DateTimeImmutable();
        }

        return $this;
    }

    public function getSignatureFilename(): ?string
    {
        return $this->signatureFilename;
    }

    public function setSignatureFilename(?string $signatureFilename): self
    {
        $this->signatureFilename = $signatureFilename;

        return $this;
    }

    /*
     * Implementation of \Serializable interface.
     *
     * The user is serialized in the session to keep the user logged in and we
     * cannot serialize the signature image file attached to a user. Therefore
     * we implement the \Serializable interface.
     *
     * In PHP 8.1 we must also implement __serialize() and __unserialize() (cf.
     * https://www.php.net/manual/en/class.serializable.php#class.serializable).
     */

    public function serialize()
    {
        return serialize($this->__serialize());
    }

    public function unserialize(string $data)
    {
        $this->__unserialize(unserialize($data));
    }

    public function __serialize(): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'roles' => $this->roles,
            'name' => $this->name,
            'initials' => $this->initials,
            'shortcuts' => $this->shortcuts,
            'signatureFilename' => $this->signatureFilename,
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->id = $data['id'];
        $this->email = $data['email'];
        $this->roles = $data['roles'];
        $this->name = $data['name'];
        $this->initials = $data['initials'];
        $this->shortcuts = $data['shortcuts'];
        $this->signatureFilename = $data['signatureFilename'];
    }
}
