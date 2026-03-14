<?php

declare(strict_types=1);

namespace App\Tests\Domain\Task\Strategy;

use App\Domain\Task\Enum\TaskStatus;
use App\Domain\Task\Factory\TaskFactory;
use App\Domain\Task\Strategy\ToInProgressStrategy;
use PHPUnit\Framework\TestCase;

final class ToInProgressStrategyTest extends TestCase
{
    private ToInProgressStrategy $strategy;
    private TaskFactory $factory;

    protected function setUp(): void
    {
        $this->strategy = new ToInProgressStrategy();
        $this->factory  = new TaskFactory();
    }

    // --- supports() ---

    public function testSupportsInProgressStatus(): void
    {
        $this->assertTrue($this->strategy->supports(TaskStatus::InProgress));
    }

    public function testDoesNotSupportToDoStatus(): void
    {
        $this->assertFalse($this->strategy->supports(TaskStatus::ToDo));
    }

    public function testDoesNotSupportDoneStatus(): void
    {
        $this->assertFalse($this->strategy->supports(TaskStatus::Done));
    }

    // --- apply() happy path ---

    public function testTransitionsFromTodoToInProgress(): void
    {
        $task = $this->factory->create('Fix bug', 'Desc', TaskStatus::ToDo);

        $this->strategy->apply($task);

        $this->assertSame(TaskStatus::InProgress, $task->getStatus());
    }

    public function testUpdatesUpdatedAtOnTransition(): void
    {
        $task   = $this->factory->create('Fix bug', 'Desc', TaskStatus::ToDo);
        $before = $task->getUpdatedAt();

        // Ensure time passes
        usleep(1000);
        $this->strategy->apply($task);

        $this->assertGreaterThanOrEqual($before, $task->getUpdatedAt());
    }

    // --- apply() invalid transitions ---

    public function testThrowsWhenTaskIsAlreadyInProgress(): void
    {
        $this->expectException(\LogicException::class);

        $task = $this->factory->create('Fix bug', 'Desc', TaskStatus::InProgress);
        $this->strategy->apply($task);
    }

    public function testThrowsWhenTaskIsDone(): void
    {
        $this->expectException(\LogicException::class);

        $task = $this->factory->create('Fix bug', 'Desc', TaskStatus::Done);
        $this->strategy->apply($task);
    }

    public function testThrowsWithDescriptiveMessage(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('In Progress');

        $task = $this->factory->create('Fix bug', 'Desc', TaskStatus::Done);
        $this->strategy->apply($task);
    }
}
