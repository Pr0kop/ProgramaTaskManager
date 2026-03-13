<?php

declare(strict_types=1);

namespace App\Application\Task\Handler;

use App\Application\Task\Command\UpdateTaskStatusCommand;
use App\Domain\Task\Enum\TaskStatus;
use App\Domain\Task\EventStore\EventStoreInterface;
use App\Domain\Task\Event\TaskStatusUpdatedEvent;
use App\Domain\Task\Repository\TaskRepositoryInterface;
use App\Domain\Task\Strategy\StatusTransitionStrategyInterface;
use App\Domain\Task\ValueObject\TaskId;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
final class UpdateTaskStatusHandler
{
    /**
     * @param iterable<StatusTransitionStrategyInterface> $strategies
     */
    public function __construct(
        private readonly TaskRepositoryInterface $taskRepository,
        private readonly EventStoreInterface $eventStore,
        private readonly MessageBusInterface $eventBus,
        private readonly iterable $strategies,
    ) {}

    public function __invoke(UpdateTaskStatusCommand $command): void
    {
        $task = $this->taskRepository->findById(TaskId::fromString($command->taskId));

        if ($task === null) {
            throw new \InvalidArgumentException("Task not found: '{$command->taskId}'");
        }

        $targetStatus   = TaskStatus::from($command->newStatus);
        $previousStatus = $task->getStatus();

        $strategy = null;
        foreach ($this->strategies as $candidate) {
            if ($candidate->supports($targetStatus)) {
                $strategy = $candidate;
                break;
            }
        }

        if ($strategy === null) {
            throw new \RuntimeException("No transition strategy found for status: '{$command->newStatus}'");
        }

        $strategy->apply($task);

        $this->taskRepository->save($task);

        $this->eventStore->append(
            aggregateId: $task->getId()->value,
            eventType:   TaskStatusUpdatedEvent::class,
            payload:     [
                'from' => $previousStatus->value,
                'to'   => $task->getStatus()->value,
            ],
        );

        $this->eventBus->dispatch(new TaskStatusUpdatedEvent(
            taskId:         $task->getId()->value,
            previousStatus: $previousStatus->value,
            newStatus:      $task->getStatus()->value,
        ));
    }
}
