<?php

declare(strict_types=1);

namespace App\Domain\User\Factory;

use App\Domain\User\Entity\User;
use Symfony\Component\Uid\Uuid;

final class UserFactory
{
    public function createFromJsonPlaceholder(array $data): User
    {
        return new User(
            id: Uuid::v4()->toRfc4122(),
            externalId: $data['id'],
            name: $data['name'],
            username: $data['username'],
            email: $data['email'],
            phone: $data['phone'] ?? null,
            website: $data['website'] ?? null,
        );
    }
}
