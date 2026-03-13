<?php

declare(strict_types=1);

namespace App\Infrastructure\GraphQL\Resolver;

use App\Application\Task\Command\CreateTaskCommand;
use App\Application\Task\Command\UpdateTaskStatusCommand;
use App\Domain\Task\Entity\Task;
use App\Domain\Task\Repository\TaskRepositoryInterface;
use App\Domain\Task\ValueObject\TaskId;
use App\Domain\Task\EventStore\EventStoreInterface;
use App\Domain\User\Entity\User;
use App\Domain\User\Repository\UserRepositoryInterface;
use App\Domain\User\ValueObject\UserId;
use App\Infrastructure\Task\Persistence\StoredEvent;
use Overblog\GraphQLBundle\Definition\Argument;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;

final class TaskResolver
{
    public function __construct(
        private readonly TaskRepositoryInterface $taskRepository,
        private readonly EventStoreInterface $eventStore,
        private readonly UserRepositoryInterface $userRepository,
        private readonly MessageBusInterface $messageBus,
    ) {}

    /** @return Task[] */
    public function resolveList(): array
    {
        return $this->taskRepository->findAll();
    }

    public function resolveItem(string $id): ?Task
    {
        try {
            return $this->taskRepository->findById(TaskId::fromString($id));
        } catch (\InvalidArgumentException) {
            return null;
        }
    }

    /** @return Task[] */
    public function resolveByUser(string $userId): array
    {
        try {
            return $this->taskRepository->findByUserId(UserId::fromString($userId));
        } catch (\InvalidArgumentException) {
            return [];
        }
    }

    /** @return array<mixed> */
    public function resolveHistory(string $id): array
    {
        return $this->eventStore->findByAggregateId($id);
    }

    public function resolveEventPayload(StoredEvent $event): string
    {
        return json_encode($event->getPayload(), JSON_THROW_ON_ERROR);
    }

    public function resolveAssignedUser(Task $task): ?User
    {
        $userId = $task->getAssignedUserId();

        if ($userId === null) {
            return null;
        }

        return $this->userRepository->findById($userId);
    }

    public function createTask(Argument $args): Task
    {
        $envelope = $this->messageBus->dispatch(new CreateTaskCommand(
            title:          $args['title'],
            description:    $args['description'] ?? '',
            status:         'todo',
            assignedUserId: $args['assignedUserId'] ?? null,
        ));

        $taskId = $envelope->last(HandledStamp::class)?->getResult();

        return $this->taskRepository->findById(TaskId::fromString($taskId));
    }

    public function updateTaskStatus(Argument $args): Task
    {
        $this->messageBus->dispatch(new UpdateTaskStatusCommand(
            taskId:    $args['id'],
            newStatus: $args['status'],
        ));

        return $this->taskRepository->findById(TaskId::fromString($args['id']));
    }
}
