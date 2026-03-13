<?php

declare(strict_types=1);

namespace App\Domain\Task\Repository;

use App\Domain\Task\Entity\Task;
use App\Domain\Task\ValueObject\TaskId;
use App\Domain\User\ValueObject\UserId;

interface TaskRepositoryInterface
{
    public function save(Task $task): void;

    public function findById(TaskId $id): ?Task;

    public function findAll(): array;

    public function findByUserId(UserId $userId): array;
}
