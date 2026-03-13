<?php

declare(strict_types=1);

namespace App\Domain\Task\Entity;

use App\Domain\Task\Enum\TaskStatus;
use App\Domain\Task\ValueObject\TaskId;
use App\Domain\User\ValueObject\UserId;
use App\Infrastructure\Task\Persistence\DoctrineTaskRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DoctrineTaskRepository::class)]
#[ORM\Table(name: 'tasks')]
class Task
{
    #[ORM\Id]
    #[ORM\Column(type: 'task_id', length: 36)]
    private TaskId $id;

    #[ORM\Column(type: 'string', length: 255)]
    private string $title;

    #[ORM\Column(type: 'text')]
    private string $description;

    #[ORM\Column(type: 'string', length: 20, enumType: TaskStatus::class)]
    private TaskStatus $status;

    #[ORM\Column(name: 'assigned_user_id', type: 'user_id', length: 36, nullable: true)]
    private ?UserId $assignedUserId;

    #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(name: 'updated_at', type: 'datetime_immutable')]
    private \DateTimeImmutable $updatedAt;

    public function __construct(
        TaskId $id,
        string $title,
        string $description,
        TaskStatus $status = TaskStatus::ToDo,
        ?UserId $assignedUserId = null,
    ) {
        if (trim($title) === '') {
            throw new \InvalidArgumentException('Task title cannot be empty.');
        }

        $this->id             = $id;
        $this->title          = $title;
        $this->description    = $description;
        $this->status         = $status;
        $this->assignedUserId = $assignedUserId;
        $this->createdAt      = new \DateTimeImmutable();
        $this->updatedAt      = new \DateTimeImmutable();
    }

    public function updateStatus(TaskStatus $newStatus): void
    {
        $this->status    = $newStatus;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): TaskId
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getStatus(): TaskStatus
    {
        return $this->status;
    }

    public function getAssignedUserId(): ?UserId
    {
        return $this->assignedUserId;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }
}
