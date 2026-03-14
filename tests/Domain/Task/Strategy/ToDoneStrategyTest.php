<?php

declare(strict_types=1);

namespace App\Tests\Domain\Task\Strategy;

use App\Domain\Task\Enum\TaskStatus;
use App\Domain\Task\Factory\TaskFactory;
use App\Domain\Task\Strategy\ToDoneStrategy;
use PHPUnit\Framework\TestCase;

final class ToDoneStrategyTest extends TestCase
{
    private ToDoneStrategy $strategy;
    private TaskFactory $factory;

    protected function setUp(): void
    {
        $this->strategy = new ToDoneStrategy();
        $this->factory  = new TaskFactory();
    }

    // --- supports() ---

    public function testSupportsDoneStatus(): void
    {
        $this->assertTrue($this->strategy->supports(TaskStatus::Done));
    }

    public function testDoesNotSupportToDoStatus(): void
    {
        $this->assertFalse($this->strategy->supports(TaskStatus::ToDo));
    }

    public function testDoesNotSupportInProgressStatus(): void
    {
        $this->assertFalse($this->strategy->supports(TaskStatus::InProgress));
    }

    // --- apply() happy path ---

    public function testTransitionsFromInProgressToDone(): void
    {
        $task = $this->factory->create('Fix bug', 'Desc', TaskStatus::InProgress);

        $this->strategy->apply($task);

        $this->assertSame(TaskStatus::Done, $task->getStatus());
    }

    public function testUpdatesUpdatedAtOnTransition(): void
    {
        $task   = $this->factory->create('Fix bug', 'Desc', TaskStatus::InProgress);
        $before = $task->getUpdatedAt();

        usleep(1000);
        $this->strategy->apply($task);

        $this->assertGreaterThanOrEqual($before, $task->getUpdatedAt());
    }

    // --- apply() invalid transitions ---

    public function testThrowsWhenTaskIsToDo(): void
    {
        $this->expectException(\LogicException::class);

        $task = $this->factory->create('Fix bug', 'Desc', TaskStatus::ToDo);
        $this->strategy->apply($task);
    }

    public function testThrowsWhenTaskIsAlreadyDone(): void
    {
        $this->expectException(\LogicException::class);

        $task = $this->factory->create('Fix bug', 'Desc', TaskStatus::Done);
        $this->strategy->apply($task);
    }

    public function testThrowsWithDescriptiveMessage(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Done');

        $task = $this->factory->create('Fix bug', 'Desc', TaskStatus::ToDo);
        $this->strategy->apply($task);
    }
}
