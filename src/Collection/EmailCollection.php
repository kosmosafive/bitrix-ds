<?php

declare(strict_types=1);

namespace Kosmosafive\Bitrix\DS\Collection;

use InvalidArgumentException;
use Kosmosafive\Bitrix\DS\Collection;
use Kosmosafive\Bitrix\DS\ValueObject\Email;

/**
 * @template-extends Collection<Email>
 */
class EmailCollection extends Collection
{
    /**
     * @param Email $value
     *
     * @return EmailCollection
     */
    public function add(mixed $value): EmailCollection
    {
        if (!$value instanceof Email) {
            throw new InvalidArgumentException("This collection only accepts instances of " . Email::class);
        }

        $this->values[$value->toString()] = $value;

        return $this;
    }

    public function get(string $email): ?Email
    {
        return $this->values[$email] ?? null;
    }

    public function has(string|Email $email): bool
    {
        return isset($this->values[(string) $email]);
    }

    public function toArray(): array
    {
        return array_keys($this->values);
    }
}
