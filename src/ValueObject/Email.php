<?php

declare(strict_types=1);

namespace Kosmosafive\Bitrix\DS\ValueObject;

use InvalidArgumentException;
use Throwable;

readonly class Email
{
    protected string $value;

    public function __construct(string $value)
    {
        if (!check_email($value)) {
            throw new InvalidArgumentException("Invalid email: {$value}");
        }

        $this->value = $value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function toString(): string
    {
        return (string) $this;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public static function tryFrom(mixed $value): ?self
    {
        try {
            return new self((string) $value);
        } catch (Throwable) {
        }

        return null;
    }
}
