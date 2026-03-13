<?php

declare(strict_types=1);

namespace App\Infrastructure\User\Controller;

use App\Application\User\Command\ImportUsersCommand;
use App\Domain\User\Repository\UserRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/users')]
final class UserController extends AbstractController
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly MessageBusInterface $messageBus,
    ) {}

    #[Route('', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $users = $this->userRepository->findAll();

        return $this->json(array_map(
            fn ($user) => [
                'id'         => $user->getId(),
                'externalId' => $user->getExternalId(),
                'name'       => $user->getName(),
                'username'   => $user->getUsername(),
                'email'      => $user->getEmail()->value,
                'role'       => $user->getRole()->value,
                'phone'      => $user->getPhone(),
                'website'    => $user->getWebsite(),
            ],
            $users
        ));
    }

    #[Route('/{id}', methods: ['GET'])]
    public function get(string $id): JsonResponse
    {
        $user = $this->userRepository->findById($id);

        if ($user === null) {
            return $this->json(['error' => 'User not found'], 404);
        }

        return $this->json([
            'id'         => $user->getId(),
            'externalId' => $user->getExternalId(),
            'name'       => $user->getName(),
            'username'   => $user->getUsername(),
            'email'      => $user->getEmail()->value,
            'role'       => $user->getRole()->value,
            'phone'      => $user->getPhone(),
            'website'    => $user->getWebsite(),
        ]);
    }

    #[Route('/import', methods: ['POST'])]
    public function import(): JsonResponse
    {
        $envelope = $this->messageBus->dispatch(new ImportUsersCommand());
        $stamp = $envelope->last(HandledStamp::class);
        $imported = $stamp?->getResult() ?? 0;

        return $this->json([
            'message'  => 'Import completed',
            'imported' => $imported,
        ]);
    }
}
