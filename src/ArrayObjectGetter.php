<?php

declare(strict_types=1);

namespace Kosmosafive\Bitrix\DS;

/**
 * @method offsetGet(string $key)
 */
trait ArrayObjectGetter
{
    public function __call(string $name, array $arguments)
    {
        if (str_starts_with($name, 'get')) {
            $key = lcfirst(substr($name, 3));
            return $this->offsetGet($key);
        }

        if (str_starts_with($name, 'is')) {
            $key = lcfirst(substr($name, 2));
            return (bool) $this->offsetGet($key);
        }
    }
}
