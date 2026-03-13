<?php

declare(strict_types=1);

namespace App\Domain\Task\Strategy;

use App\Domain\Task\Entity\Task;
use App\Domain\Task\Enum\TaskStatus;

final class ToDoneStrategy implements StatusTransitionStrategyInterface
{
    public function supports(TaskStatus $targetStatus): bool
    {
        return $targetStatus === TaskStatus::Done;
    }

    public function apply(Task $task): void
    {
        if ($task->getStatus() !== TaskStatus::InProgress) {
            throw new \LogicException(sprintf(
                'Cannot transition to "Done" from "%s". Task must be "In Progress".',
                $task->getStatus()->value,
            ));
        }

        $task->updateStatus(TaskStatus::Done);
    }
}
