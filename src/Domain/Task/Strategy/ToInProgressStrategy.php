<?php

declare(strict_types=1);

namespace App\Domain\Task\Strategy;

use App\Domain\Task\Entity\Task;
use App\Domain\Task\Enum\TaskStatus;

final class ToInProgressStrategy implements StatusTransitionStrategyInterface
{
    public function supports(TaskStatus $targetStatus): bool
    {
        return $targetStatus === TaskStatus::InProgress;
    }

    public function apply(Task $task): void
    {
        if ($task->getStatus() !== TaskStatus::ToDo) {
            throw new \LogicException(sprintf(
                'Cannot transition to "In Progress" from "%s". Task must be in "To Do" state.',
                $task->getStatus()->value,
            ));
        }

        $task->updateStatus(TaskStatus::InProgress);
    }
}
