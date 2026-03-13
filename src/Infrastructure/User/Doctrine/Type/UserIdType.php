<?php

declare(strict_types=1);

namespace App\Infrastructure\User\Doctrine\Type;

use App\Domain\User\ValueObject\UserId;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;

final class UserIdType extends StringType
{
    public const NAME = 'user_id';

    public function getName(): string
    {
        return self::NAME;
    }

    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?UserId
    {
        if ($value === null) {
            return null;
        }

        return UserId::fromString((string) $value);
    }

    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof UserId) {
            return $value->value;
        }

        return (string) $value;
    }
}
