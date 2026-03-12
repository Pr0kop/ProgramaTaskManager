<?php

declare(strict_types=1);

namespace App\Domain\User\Repository;

use App\Domain\User\Entity\User;

interface UserRepositoryInterface
{
    public function save(User $user): void;

    public function findById(string $id): ?User;

    public function findByExternalId(int $externalId): ?User;

    public function findByEmail(string $email): ?User;

    /** @return User[] */
    public function findAll(): array;
}
