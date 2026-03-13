<?php

declare(strict_types=1);

namespace App\Domain\User\ValueObject;

final readonly class Email
{
    private function __construct(
        public readonly string $value,
    ) {}

    public static function fromString(string $email): self
    {
        $normalized = strtolower(trim($email));

        if ($normalized === '') {
            throw new \InvalidArgumentException('Email cannot be empty.');
        }

        if (!filter_var($normalized, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException("Invalid email address: '{$email}'");
        }

        return new self($normalized);
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
