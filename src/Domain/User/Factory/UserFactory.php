<?php

declare(strict_types=1);

namespace App\Domain\User\Factory;

use App\Domain\User\Entity\User;
use App\Domain\User\Enum\UserRole;
use App\Domain\User\ValueObject\Email;
use App\Domain\User\ValueObject\UserId;

final class UserFactory
{
    public function createFromJsonPlaceholder(array $data, string $hashedPassword, string $apiToken, UserRole $role = UserRole::Member): User
    {
        $user = new User(
            id:         UserId::generate(),
            name:       $data['name'],
            username:   $data['username'],
            email:      Email::fromString($data['email']),
            role:       $role,
            externalId: $data['id'],
            phone:      $data['phone'] ?? null,
            website:    $data['website'] ?? null,
        );

        $user->setPassword($hashedPassword);
        $user->setApiToken($apiToken);

        return $user;
    }
}
