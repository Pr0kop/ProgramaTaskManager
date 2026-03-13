<?php

declare(strict_types=1);

namespace App\Domain\Task\Strategy;

use App\Domain\Task\Entity\Task;
use App\Domain\Task\Enum\TaskStatus;

interface StatusTransitionStrategyInterface
{
    public function supports(TaskStatus $targetStatus): bool;

    public function apply(Task $task): void;
}
