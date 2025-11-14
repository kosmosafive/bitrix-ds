<?php

declare(strict_types=1);

namespace Kosmosafive\Bitrix\DS\Collection;

use InvalidArgumentException;
use Kosmosafive\Bitrix\DS\Collection;

/**
 * @template-extends Collection<int>
 */
class NumericIdCollection extends Collection
{
    public function add(mixed $value): NumericIdCollection
    {
        $filteredValue = filter_var($value, FILTER_VALIDATE_INT, [
            'flags' => FILTER_NULL_ON_FAILURE,
            'options' => [
                'min_range' => 1,
            ],
        ]);

        if (!$filteredValue) {
            throw new InvalidArgumentException("This collection only accepts positive integer");
        }

        $this->values[$filteredValue] = $filteredValue;

        return $this;
    }

    public function toArray(): array
    {
        return array_keys($this->values);
    }
}
