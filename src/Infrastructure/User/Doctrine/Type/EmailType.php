<?php

declare(strict_types=1);

namespace App\Infrastructure\User\Doctrine\Type;

use App\Domain\User\ValueObject\Email;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;

final class EmailType extends StringType
{
    public const NAME = 'email';

    public function getName(): string
    {
        return self::NAME;
    }

    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?Email
    {
        if ($value === null) {
            return null;
        }

        return Email::fromString((string) $value);
    }

    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof Email) {
            return $value->value;
        }

        return (string) $value;
    }
}
