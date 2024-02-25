<?php

namespace Dullahan\Entity;

use Doctrine\ORM\Mapping as ORM;
use Dullahan\Repository\UserRepository;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Private user information.
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    public const ROLE_USER = 'ROLE_USER';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    private string $email;

    /** @var array<mixed> $roles */
    #[ORM\Column]
    private array $roles = [];

    /** @var string The hashed password $password */
    #[ORM\Column]
    private string $password;

    #[ORM\OneToOne(mappedBy: 'user', cascade: ['persist'])]
    private ?UserData $data = null;

    #[ORM\Column(length: 64, nullable: true)]
    private ?string $activationToken = null;

    #[ORM\Column(options: ['default' => 0])]
    private ?bool $activated = null;

    #[ORM\Column]
    private ?int $created = null;

    #[ORM\Column(nullable: true)]
    private ?int $whenActivated = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $newEmail = null;

    #[ORM\Column(length: 64, nullable: true)]
    private ?string $emailVerificationToken = null;

    #[ORM\Column(nullable: true)]
    private ?int $activationTokenExp = null;

    #[ORM\Column(nullable: true)]
    private ?int $emailVerificationTokenExp = null;

    #[ORM\Column(length: 64, nullable: true)]
    private ?string $passwordVerificationToken = null;

    #[ORM\Column(nullable: true)]
    private ?int $passwordVerificationTokenExp = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $newPassword = null;

    #[ORM\Column(length: 64, nullable: true)]
    private ?string $passwordResetVerificationToken = null;

    #[ORM\Column(nullable: true)]
    private ?int $passwordResetVerificationTokenExp = null;

    public function __construct()
    {
        $this->setCreated(time());
        $this->setActivated(false);
    }

    public function getId(): ?int
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
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = self::ROLE_USER;

        return array_unique($roles);
    }

    /**
     * @param array<mixed> $roles
     */
    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getData(): ?UserData
    {
        return $this->data;
    }

    public function setData(UserData $data): self
    {
        // set the owning side of the relation if necessary
        if ($data->getUser() !== $this) {
            $data->setUser($this);
        }

        $this->data = $data;

        return $this;
    }

    public function getActivationToken(): ?string
    {
        return $this->activationToken;
    }

    public function setActivationToken(?string $activationToken): self
    {
        $this->activationToken = $activationToken;

        return $this;
    }

    public function isActivated(): ?bool
    {
        return $this->activated;
    }

    public function setActivated(bool $activated): self
    {
        $this->activated = $activated;

        return $this;
    }

    public function getCreated(): ?int
    {
        return $this->created;
    }

    public function setCreated(int $created): self
    {
        $this->created = $created;

        return $this;
    }

    public function getWhenActivated(): ?int
    {
        return $this->whenActivated;
    }

    public function setWhenActivated(?int $whenActivated): self
    {
        $this->whenActivated = $whenActivated;

        return $this;
    }

    public function getNewEmail(): ?string
    {
        return $this->newEmail;
    }

    public function setNewEmail(?string $newEmail): self
    {
        $this->newEmail = $newEmail;

        return $this;
    }

    public function getEmailVerificationToken(): ?string
    {
        return $this->emailVerificationToken;
    }

    public function setEmailVerificationToken(?string $emailVerificationToken): self
    {
        $this->emailVerificationToken = $emailVerificationToken;

        return $this;
    }

    public function getActivationTokenExp(): ?int
    {
        return $this->activationTokenExp;
    }

    public function setActivationTokenExp(?int $activationTokenExp): self
    {
        $this->activationTokenExp = $activationTokenExp;

        return $this;
    }

    public function getEmailVerificationTokenExp(): ?int
    {
        return $this->emailVerificationTokenExp;
    }

    public function setEmailVerificationTokenExp(?int $emailVerificationTokenExp): self
    {
        $this->emailVerificationTokenExp = $emailVerificationTokenExp;

        return $this;
    }

    public function getPasswordVerificationToken(): ?string
    {
        return $this->passwordVerificationToken;
    }

    public function setPasswordVerificationToken(?string $passwordVerificationToken): self
    {
        $this->passwordVerificationToken = $passwordVerificationToken;

        return $this;
    }

    public function getPasswordVerificationTokenExp(): ?int
    {
        return $this->passwordVerificationTokenExp;
    }

    public function setPasswordVerificationTokenExp(?int $passwordVerificationTokenExp): self
    {
        $this->passwordVerificationTokenExp = $passwordVerificationTokenExp;

        return $this;
    }

    public function getNewPassword(): ?string
    {
        return $this->newPassword;
    }

    public function setNewPassword(?string $newPassword): self
    {
        $this->newPassword = $newPassword;

        return $this;
    }

    public function getPasswordResetVerificationToken(): ?string
    {
        return $this->passwordResetVerificationToken;
    }

    public function setPasswordResetVerificationToken(?string $passwordResetVerificationToken): self
    {
        $this->passwordResetVerificationToken = $passwordResetVerificationToken;

        return $this;
    }

    public function getPasswordResetVerificationTokenExp(): ?int
    {
        return $this->passwordResetVerificationTokenExp;
    }

    public function setPasswordResetVerificationTokenExp(?int $passwordResetVerificationTokenExp): self
    {
        $this->passwordResetVerificationTokenExp = $passwordResetVerificationTokenExp;

        return $this;
    }
}
