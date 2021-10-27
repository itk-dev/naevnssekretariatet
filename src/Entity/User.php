<?php

namespace App\Entity;

use App\Logging\LoggableEntityInterface;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidV4Generator;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Uid\UuidV4;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 */
class User implements UserInterface, LoggableEntityInterface
{
    /**
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class=UuidV4Generator::class)
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
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity=Municipality::class)
     */
    private $favoriteMunicipality;

    /**
     * @ORM\OneToMany(targetEntity=Reminder::class, mappedBy="createdBy")
     */
    private $reminders;

    public function __construct()
    {
        $this->reminders = new ArrayCollection();
    }

    public function getId(): ?UuidV4
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
}
