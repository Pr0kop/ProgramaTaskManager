<?php

declare(strict_types=1);

namespace App\Domain\Task\Event;

final readonly class TaskStatusUpdatedEvent
{
    public function __construct(
        public string $taskId,
        public string $previousStatus,
        public string $newStatus,
        public \DateTimeImmutable $occurredAt = new \DateTimeImmutable(),
    ) {}
}
