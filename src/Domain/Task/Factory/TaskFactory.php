<?php

declare(strict_types=1);

namespace App\Domain\Task\Factory;

use App\Domain\Task\Entity\Task;
use App\Domain\Task\Enum\TaskStatus;
use App\Domain\Task\ValueObject\TaskId;
use App\Domain\User\ValueObject\UserId;

final class TaskFactory
{
    public function create(
        string $title,
        string $description,
        TaskStatus $status = TaskStatus::ToDo,
        ?string $assignedUserId = null,
    ): Task {
        return new Task(
            id:             TaskId::generate(),
            title:          $title,
            description:    $description,
            status:         $status,
            assignedUserId: $assignedUserId !== null ? UserId::fromString($assignedUserId) : null,
        );
    }
}
