<?php

declare(strict_types=1);

namespace App\Application\User\Handler;

use App\Application\User\Command\ImportUsersCommand;
use App\Domain\User\Enum\UserRole;
use App\Domain\User\Factory\UserFactory;
use App\Domain\User\Repository\UserRepositoryInterface;
use App\Infrastructure\User\External\JsonPlaceholderClient;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsMessageHandler]
final class ImportUsersHandler
{
    private const ADMIN_EXTERNAL_ID = 1;

    public function __construct(
        private readonly JsonPlaceholderClient $client,
        private readonly UserRepositoryInterface $userRepository,
        private readonly UserFactory $userFactory,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly string $defaultUserPassword,
    ) {}

    public function __invoke(ImportUsersCommand $command): int
    {
        $data = $this->client->fetchUsers();
        $imported = 0;

        foreach ($data as $userData) {
            if ($this->userRepository->findByExternalId($userData['id']) !== null) {
                continue;
            }

            $role     = $userData['id'] === self::ADMIN_EXTERNAL_ID ? UserRole::Admin : UserRole::Member;
            $apiToken = bin2hex(random_bytes(32));

            // Create user first with empty password to use as context for hasher
            $user = $this->userFactory->createFromJsonPlaceholder($userData, '', $apiToken, $role);
            $hashedPassword = $this->passwordHasher->hashPassword($user, $this->defaultUserPassword);
            $user->setPassword($hashedPassword);

            $this->userRepository->save($user);
            $imported++;
        }

        return $imported;
    }
}
