<?php

declare(strict_types=1);

namespace App\Infrastructure\Task\Persistence;

use App\Domain\Task\EventStore\EventStoreInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;

final class DoctrineEventStore implements EventStoreInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {}

    public function append(string $aggregateId, string $eventType, array $payload): void
    {
        $storedEvent = new StoredEvent(
            id:          Uuid::v4()->toRfc4122(),
            aggregateId: $aggregateId,
            eventType:   $eventType,
            payload:     $payload,
        );

        $this->entityManager->persist($storedEvent);
        $this->entityManager->flush();
    }

    public function findByAggregateId(string $aggregateId): array
    {
        return $this->entityManager
            ->getRepository(StoredEvent::class)
            ->findBy(
                ['aggregateId' => $aggregateId],
                ['occurredAt'  => 'ASC'],
            );
    }
}
