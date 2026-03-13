<?php

declare(strict_types=1);

namespace App\Infrastructure\Task\Controller;

use App\Application\Task\Command\CreateTaskCommand;
use App\Application\Task\Command\UpdateTaskStatusCommand;
use App\Domain\Task\EventStore\EventStoreInterface;
use App\Domain\Task\Repository\TaskRepositoryInterface;
use App\Domain\Task\ValueObject\TaskId;
use App\Domain\User\ValueObject\UserId;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/tasks')]
final class TaskController extends AbstractController
{
    public function __construct(
        private readonly TaskRepositoryInterface $taskRepository,
        private readonly EventStoreInterface $eventStore,
        private readonly MessageBusInterface $messageBus,
    ) {}

    #[Route('', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = $request->toArray();

        if (empty($data['title'])) {
            return $this->json(['error' => 'Title is required'], 400);
        }

        $envelope = $this->messageBus->dispatch(new CreateTaskCommand(
            title:          $data['title'],
            description:    $data['description'] ?? '',
            status:         $data['status'] ?? 'todo',
            assignedUserId: $data['assignedUserId'] ?? null,
        ));

        $taskId = $envelope->last(HandledStamp::class)?->getResult();

        return $this->json(['id' => $taskId], 201);
    }

    #[Route('', methods: ['GET'])]
    public function listAll(): JsonResponse
    {
        $tasks = $this->taskRepository->findAll();

        return $this->json(array_map(fn ($task) => $this->serialize($task), $tasks));
    }

    #[Route('/user/{userId}', methods: ['GET'])]
    public function listByUser(string $userId): JsonResponse
    {
        try {
            $userIdVO = UserId::fromString($userId);
        } catch (\InvalidArgumentException) {
            return $this->json(['error' => 'Invalid user ID format'], 400);
        }

        $tasks = $this->taskRepository->findByUserId($userIdVO);

        return $this->json(array_map(fn ($task) => $this->serialize($task), $tasks));
    }

    #[Route('/{id}/status', methods: ['PATCH'])]
    public function updateStatus(string $id, Request $request): JsonResponse
    {
        $data = $request->toArray();

        if (empty($data['status'])) {
            return $this->json(['error' => 'Status is required'], 400);
        }

        try {
            $this->messageBus->dispatch(new UpdateTaskStatusCommand(
                taskId:    $id,
                newStatus: $data['status'],
            ));
        } catch (HandlerFailedException $e) {
            $cause = $e->getPrevious();
            if ($cause instanceof \InvalidArgumentException) {
                return $this->json(['error' => $cause->getMessage()], 404);
            }
            if ($cause instanceof \LogicException) {
                return $this->json(['error' => $cause->getMessage()], 422);
            }
            throw $e;
        }

        return $this->json(['message' => 'Status updated']);
    }

    #[Route('/{id}/history', methods: ['GET'])]
    public function history(string $id): JsonResponse
    {
        try {
            TaskId::fromString($id);
        } catch (\InvalidArgumentException) {
            return $this->json(['error' => 'Invalid task ID format'], 400);
        }

        $events = $this->eventStore->findByAggregateId($id);

        return $this->json(array_map(
            fn ($event) => [
                'eventType'  => $event->getEventType(),
                'payload'    => $event->getPayload(),
                'occurredAt' => $event->getOccurredAt()->format(\DateTimeInterface::ATOM),
            ],
            $events,
        ));
    }

    private function serialize(mixed $task): array
    {
        return [
            'id'             => $task->getId()->value,
            'title'          => $task->getTitle(),
            'description'    => $task->getDescription(),
            'status'         => $task->getStatus()->value,
            'assignedUserId' => $task->getAssignedUserId()?->value,
            'createdAt'      => $task->getCreatedAt()->format(\DateTimeInterface::ATOM),
            'updatedAt'      => $task->getUpdatedAt()->format(\DateTimeInterface::ATOM),
        ];
    }
}
