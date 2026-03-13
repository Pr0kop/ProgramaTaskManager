<?php

declare(strict_types=1);

namespace App\Infrastructure\Task\Doctrine\Type;

use App\Domain\Task\ValueObject\TaskId;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;

final class TaskIdType extends StringType
{
    public const NAME = 'task_id';

    public function getName(): string
    {
        return self::NAME;
    }

    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?TaskId
    {
        if ($value === null) {
            return null;
        }

        return TaskId::fromString((string) $value);
    }

    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof TaskId) {
            return $value->value;
        }

        return (string) $value;
    }
}
