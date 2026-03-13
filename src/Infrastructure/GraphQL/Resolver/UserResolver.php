<?php

declare(strict_types=1);

namespace App\Infrastructure\GraphQL\Resolver;

use App\Domain\User\Entity\User;
use App\Domain\User\Repository\UserRepositoryInterface;
use App\Domain\User\ValueObject\UserId;

final class UserResolver
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
    ) {}

    /** @return User[] */
    public function resolveList(): array
    {
        return $this->userRepository->findAll();
    }

    public function resolveItem(string $id): ?User
    {
        try {
            return $this->userRepository->findById(UserId::fromString($id));
        } catch (\InvalidArgumentException) {
            return null;
        }
    }
}
