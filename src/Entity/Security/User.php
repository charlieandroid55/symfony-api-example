<?php

declare(strict_types=1);


namespace App\Entity\Security;

use App\Repository\Security\UserRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'app_user')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    protected ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 100)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 5, max: 100, minMessage: 'user.email.min_length', maxMessage: 'user.email.max_length')]
    #[Assert\Email(message: 'user.email.invalid', mode: Assert\Email::VALIDATION_MODE_STRICT)]
    private ?string $email;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    #[Assert\Length(min: 1, max: 100, minMessage: 'user.name.min_length', maxMessage: 'user.name.max_length')]
    private ?string $name = null;

    #[ORM\Column(type: Types::STRING)]
    #[Assert\Length(min: 1, max: 100, minMessage: 'user.lastname.min_length', maxMessage: 'user.lastname.max_length')]
    private ?string $lastname = null;

    #[ORM\Column(type: Types::BOOLEAN)]
    #[Groups(['user_read', 'user_write', 'read', 'patient_write'])]
    private bool $enabled;

    #[ORM\Column(type: Types::STRING, length: 2)]
    #[Assert\Length(min: 2, max: 2, minMessage: 'user.locale.min_length', maxMessage: 'user.locale.max_length')]
    #[Assert\Locale(message: 'user.locale.invalid')]
    private string $locale;

    /**
     * @var array<string>
     */
    #[ORM\Column(type: Types::JSON)]
    #[Groups(['user_read', 'user_write', 'read', 'patient_write'])]
    private array $roles;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    #[Groups(['user_write', 'patient_write'])]
    private ?string $password = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(['user_read', 'read'])]
    private ?\DateTimeInterface $lastLogin = null;

    /**
     * User constructor.
     *
     * @param array<string> $roles
     */
    public function __construct(array $roles = [], string $email = null)
    {
        $this->roles = $roles;
        $this->email = $email;
        $this->locale = 'ES';
        $this->enabled = false;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function setLocale(string $locale): self
    {
        $this->locale = $locale;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): self
    {
        $this->password = $password;

        return $this;
    }


    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * @codeCoverageIgnore
     */
    public function eraseCredentials(): void
    {
    }

    public function getEnabled(): ?bool
    {
        return $this->enabled;
    }

    /**
     * @return $this
     */
    public function addRoles(string $role): self
    {
        $role = strtoupper($role);

        if (!\in_array($role, $this->roles, true)) {
            $this->roles[] = $role;
        }

        return $this;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function hasRoles(string $role): bool
    {
        return \in_array(strtoupper($role), $this->getRoles(), true);
    }

    /**
     * @return $this
     */
    public function removeRoles(string $role): self
    {
        if (false !== $key = array_search(strtoupper($role), $this->roles, true)) {
            unset($this->roles[$key]);
            $this->roles = array_values($this->roles);
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function setRoles(mixed $roles): self
    {
        $this->roles = [];

        foreach ($roles as $role) {
            $this->addRoles($role);
        }

        return $this;
    }


    public function getLastLogin(): ?\DateTimeInterface
    {
        return $this->lastLogin;
    }

    public function setLastLogin(?\DateTimeInterface $lastLogin): self
    {
        $this->lastLogin = $lastLogin;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    /**
     * @return array<mixed>
     */
    public function getJWTInfo(): array
    {
        return [
            'lastLogin' => $this->getLastLogin()?->format(\DateTimeInterface::ATOM),
            'locale' => \strtolower($this->getLocale()),
        ];
    }

}
