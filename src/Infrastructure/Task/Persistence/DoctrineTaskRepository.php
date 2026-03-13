<?php

declare(strict_types=1);

namespace App\Infrastructure\Task\Persistence;

use App\Domain\Task\Entity\Task;
use App\Domain\Task\Repository\TaskRepositoryInterface;
use App\Domain\Task\ValueObject\TaskId;
use App\Domain\User\ValueObject\UserId;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class DoctrineTaskRepository extends ServiceEntityRepository implements TaskRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Task::class);
    }

    public function save(Task $task): void
    {
        $this->getEntityManager()->persist($task);
        $this->getEntityManager()->flush();
    }

    public function findById(TaskId $id): ?Task
    {
        return $this->find($id->value);
    }

    public function findAll(): array
    {
        return parent::findAll();
    }

    public function findByUserId(UserId $userId): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.assignedUserId = :userId')
            ->setParameter('userId', $userId->value)
            ->getQuery()
            ->getResult();
    }
}
