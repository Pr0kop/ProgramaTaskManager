<?php

declare(strict_types=1);

namespace App\Infrastructure\User\Persistence;

use App\Domain\User\Entity\User;
use App\Domain\User\Repository\UserRepositoryInterface;
use App\Domain\User\ValueObject\Email;
use App\Domain\User\ValueObject\UserId;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class DoctrineUserRepository extends ServiceEntityRepository implements UserRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function save(User $user): void
    {
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    public function findById(UserId $id): ?User
    {
        return $this->find($id->value);
    }

    public function findByExternalId(int $externalId): ?User
    {
        return $this->findOneBy(['externalId' => $externalId]);
    }

    public function findByEmail(Email $email): ?User
    {
        return $this->findOneBy(['email' => $email->value]);
    }

    public function findAll(): array
    {
        return parent::findAll();
    }
}
