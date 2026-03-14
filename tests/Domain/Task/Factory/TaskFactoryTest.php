<?php

declare(strict_types=1);

namespace App\Tests\Domain\Task\Factory;

use App\Domain\Task\Entity\Task;
use App\Domain\Task\Enum\TaskStatus;
use App\Domain\Task\Factory\TaskFactory;
use PHPUnit\Framework\TestCase;

final class TaskFactoryTest extends TestCase
{
    private TaskFactory $factory;

    protected function setUp(): void
    {
        $this->factory = new TaskFactory();
    }

    // --- Instance ---

    public function testReturnsTaskInstance(): void
    {
        $task = $this->factory->create('Fix bug', 'Some description');

        $this->assertInstanceOf(Task::class, $task);
    }

    // --- Fields ---

    public function testSetsTitleCorrectly(): void
    {
        $task = $this->factory->create('Fix bug', 'Description');

        $this->assertSame('Fix bug', $task->getTitle());
    }

    public function testSetsDescriptionCorrectly(): void
    {
        $task = $this->factory->create('Fix bug', 'My description');

        $this->assertSame('My description', $task->getDescription());
    }

    public function testAllowsEmptyDescription(): void
    {
        $task = $this->factory->create('Fix bug', '');

        $this->assertSame('', $task->getDescription());
    }

    // --- Status ---

    public function testDefaultStatusIsToDo(): void
    {
        $task = $this->factory->create('Fix bug', 'Description');

        $this->assertSame(TaskStatus::ToDo, $task->getStatus());
    }

    public function testSetsInProgressStatus(): void
    {
        $task = $this->factory->create('Fix bug', 'Description', TaskStatus::InProgress);

        $this->assertSame(TaskStatus::InProgress, $task->getStatus());
    }

    public function testSetsDoneStatus(): void
    {
        $task = $this->factory->create('Fix bug', 'Description', TaskStatus::Done);

        $this->assertSame(TaskStatus::Done, $task->getStatus());
    }

    // --- Assigned user ---

    public function testAssignedUserIdIsNullByDefault(): void
    {
        $task = $this->factory->create('Fix bug', 'Description');

        $this->assertNull($task->getAssignedUserId());
    }

    public function testSetsAssignedUserIdWhenProvided(): void
    {
        $userId = '550e8400-e29b-41d4-a716-446655440000';

        $task = $this->factory->create('Fix bug', 'Description', TaskStatus::ToDo, $userId);

        $this->assertNotNull($task->getAssignedUserId());
        $this->assertSame($userId, $task->getAssignedUserId()->value);
    }

    public function testThrowsOnInvalidAssignedUserId(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->factory->create('Fix bug', 'Description', TaskStatus::ToDo, 'not-a-uuid');
    }

    // --- UUID ---

    public function testGeneratesUniqueIds(): void
    {
        $task1 = $this->factory->create('Task 1', 'Desc');
        $task2 = $this->factory->create('Task 2', 'Desc');

        $this->assertNotSame($task1->getId()->value, $task2->getId()->value);
    }

    public function testGeneratedIdIsValidUuid(): void
    {
        $task = $this->factory->create('Fix bug', 'Description');
        $uuid = $task->getId()->value;

        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/',
            $uuid
        );
    }

    // --- Invalid data ---

    public function testThrowsOnEmptyTitle(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->factory->create('', 'Description');
    }

    public function testThrowsOnWhitespaceOnlyTitle(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->factory->create('   ', 'Description');
    }

    // --- Timestamps ---

    public function testCreatedAtIsSetOnCreation(): void
    {
        $before = new \DateTimeImmutable();
        $task   = $this->factory->create('Fix bug', 'Description');
        $after  = new \DateTimeImmutable();

        $this->assertGreaterThanOrEqual($before, $task->getCreatedAt());
        $this->assertLessThanOrEqual($after, $task->getCreatedAt());
    }
}
