<?php

declare(strict_types=1);

namespace App\Domain\Task\EventStore;

interface EventStoreInterface
{
    public function append(string $aggregateId, string $eventType, array $payload): void;

    public function findByAggregateId(string $aggregateId): array;
}
