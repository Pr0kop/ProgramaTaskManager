<?php

declare(strict_types=1);

namespace App\Domain\Task\Event;

final readonly class TaskCreatedEvent
{
    public function __construct(
        public string $taskId,
        public string $title,
        public string $description,
        public string $status,
        public ?string $assignedUserId,
        public \DateTimeImmutable $occurredAt = new \DateTimeImmutable(),
    ) {}
}
