<?php

declare(strict_types=1);

namespace App\Domain\User\Entity;

use App\Domain\User\Enum\UserRole;
use App\Domain\User\ValueObject\Email;
use App\Infrastructure\User\Persistence\DoctrineUserRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DoctrineUserRepository::class)]
#[ORM\Table(name: 'users')]
class User
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    private string $id;

    #[ORM\Column(name: 'external_id', type: 'integer', unique: true, nullable: true)]
    private ?int $externalId;

    #[ORM\Column(type: 'string', length: 255)]
    private string $name;

    #[ORM\Column(type: 'string', length: 100)]
    private string $username;

    #[ORM\Column(type: 'email', length: 255, unique: true)]
    private Email $email;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private ?string $phone;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $website;

    #[ORM\Column(name: 'role', type: 'string', length: 20, enumType: UserRole::class)]
    private UserRole $role;

    #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    public function __construct(
        string $id,
        string $name,
        string $username,
        Email $email,
        UserRole $role = UserRole::Member,
        ?int $externalId = null,
        ?string $phone = null,
        ?string $website = null,
    ) {
        if (trim($name) === '') {
            throw new \InvalidArgumentException('User name cannot be empty.');
        }

        if (trim($username) === '') {
            throw new \InvalidArgumentException('Username cannot be empty.');
        }

        $this->id         = $id;
        $this->externalId = $externalId;
        $this->name       = $name;
        $this->username   = $username;
        $this->email      = $email;
        $this->role       = $role;
        $this->phone      = $phone;
        $this->website    = $website;
        $this->createdAt  = new \DateTimeImmutable();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getExternalId(): ?int
    {
        return $this->externalId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getEmail(): Email
    {
        return $this->email;
    }

    public function getRole(): UserRole
    {
        return $this->role;
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function getWebsite(): ?string
    {
        return $this->website;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
