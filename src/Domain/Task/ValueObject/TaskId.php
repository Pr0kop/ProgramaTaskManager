<?php

declare(strict_types=1);

namespace App\Domain\Task\ValueObject;

use Symfony\Component\Uid\Uuid;

final readonly class TaskId
{
    private function __construct(
        public readonly string $value,
    ) {}

    public static function generate(): self
    {
        return new self(Uuid::v4()->toRfc4122());
    }

    public static function fromString(string $uuid): self
    {
        if (!Uuid::isValid($uuid)) {
            throw new \InvalidArgumentException("Invalid UUID: '{$uuid}'");
        }

        return new self($uuid);
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
