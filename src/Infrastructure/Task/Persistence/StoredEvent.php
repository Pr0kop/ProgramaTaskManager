<?php

declare(strict_types=1);

namespace App\Infrastructure\Task\Persistence;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'task_events')]
class StoredEvent
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    private string $id;

    #[ORM\Column(name: 'aggregate_id', type: 'string', length: 36)]
    private string $aggregateId;

    #[ORM\Column(name: 'event_type', type: 'string', length: 255)]
    private string $eventType;

    #[ORM\Column(type: 'json')]
    private array $payload;

    #[ORM\Column(name: 'occurred_at', type: 'datetime_immutable')]
    private \DateTimeImmutable $occurredAt;

    public function __construct(
        string $id,
        string $aggregateId,
        string $eventType,
        array $payload,
    ) {
        $this->id          = $id;
        $this->aggregateId = $aggregateId;
        $this->eventType   = $eventType;
        $this->payload     = $payload;
        $this->occurredAt  = new \DateTimeImmutable();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getAggregateId(): string
    {
        return $this->aggregateId;
    }

    public function getEventType(): string
    {
        return $this->eventType;
    }

    public function getPayload(): array
    {
        return $this->payload;
    }

    public function getOccurredAt(): \DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
