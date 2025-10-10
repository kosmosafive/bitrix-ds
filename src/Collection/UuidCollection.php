<?php

declare(strict_types=1);

namespace Kosmosafive\Bitrix\DS\Collection;

use InvalidArgumentException;
use Kosmosafive\Bitrix\DS\Collection;
use Ramsey\Uuid\UuidInterface;

/**
 * @template-extends Collection<UuidInterface>
 */
class UuidCollection extends Collection
{
    /**
     * @param UuidInterface $value
     *
     * @return UuidCollection
     */
    public function add(mixed $value): UuidCollection
    {
        if (!$value instanceof UuidInterface) {
            throw new InvalidArgumentException("This collection only accepts instances of " . UuidInterface::class);
        }

        $this->values[$value->toString()] = $value;

        return $this;
    }

    public function has(UuidInterface $value): bool
    {
        return isset($this->values[$value->toString()]);
    }

    public function toArray(): array
    {
        return array_keys($this->values);
    }
}
