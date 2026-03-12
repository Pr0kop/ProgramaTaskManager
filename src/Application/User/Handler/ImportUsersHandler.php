<?php

declare(strict_types=1);

namespace App\Application\User\Handler;

use App\Application\User\Command\ImportUsersCommand;
use App\Domain\User\Factory\UserFactory;
use App\Domain\User\Repository\UserRepositoryInterface;
use App\Infrastructure\User\External\JsonPlaceholderClient;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class ImportUsersHandler
{
    public function __construct(
        private readonly JsonPlaceholderClient $client,
        private readonly UserRepositoryInterface $userRepository,
        private readonly UserFactory $userFactory,
    ) {}

    public function __invoke(ImportUsersCommand $command): int
    {
        $data = $this->client->fetchUsers();
        $imported = 0;

        foreach ($data as $userData) {
            if ($this->userRepository->findByExternalId($userData['id']) !== null) {
                continue;
            }

            $user = $this->userFactory->createFromJsonPlaceholder($userData);
            $this->userRepository->save($user);
            $imported++;
        }

        return $imported;
    }
}
