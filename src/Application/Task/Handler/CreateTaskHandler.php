<?php

declare(strict_types=1);

namespace App\Application\Task\Handler;

use App\Application\Task\Command\CreateTaskCommand;
use App\Domain\Task\Enum\TaskStatus;
use App\Domain\Task\EventStore\EventStoreInterface;
use App\Domain\Task\Event\TaskCreatedEvent;
use App\Domain\Task\Factory\TaskFactory;
use App\Domain\Task\Repository\TaskRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
final class CreateTaskHandler
{
    public function __construct(
        private readonly TaskRepositoryInterface $taskRepository,
        private readonly TaskFactory $taskFactory,
        private readonly EventStoreInterface $eventStore,
        private readonly MessageBusInterface $eventBus,
    ) {}

    public function __invoke(CreateTaskCommand $command): string
    {
        $task = $this->taskFactory->create(
            title:          $command->title,
            description:    $command->description,
            status:         TaskStatus::from($command->status),
            assignedUserId: $command->assignedUserId,
        );

        $this->taskRepository->save($task);

        $this->eventStore->append(
            aggregateId: $task->getId()->value,
            eventType:   TaskCreatedEvent::class,
            payload:     [
                'title'          => $task->getTitle(),
                'description'    => $task->getDescription(),
                'status'         => $task->getStatus()->value,
                'assignedUserId' => $task->getAssignedUserId()?->value,
            ],
        );

        $this->eventBus->dispatch(new TaskCreatedEvent(
            taskId:         $task->getId()->value,
            title:          $task->getTitle(),
            description:    $task->getDescription(),
            status:         $task->getStatus()->value,
            assignedUserId: $task->getAssignedUserId()?->value,
        ));

        return $task->getId()->value;
    }
}
