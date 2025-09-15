<?php

declare(strict_types=1);

namespace Kosmosafive\Bitrix\DS;

use ArrayAccess;
use BadMethodCallException;
use Closure;
use Countable;
use Iterator;
use ReturnTypeWillChange;

/**
 * @template T
 *
 * @extends Collection<T>
 *
 * @noinspection all
 */
abstract class Collection implements ArrayAccess, Iterator, Countable
{
    /**
     * @var array<int|string, T>
     */
    protected array $values;

    public function __construct(mixed ...$values)
    {
        foreach ($values as $value) {
            $this->add($value);
        }
    }

    /**
     * @return T|null
     */
    #[ReturnTypeWillChange]
    public function current()
    {
        return current($this->values);
    }

    public function next(): void
    {
        next($this->values);
    }

    /**
     * @return int|string|null
     */
    #[ReturnTypeWillChange]
    public function key()
    {
        return key($this->values);
    }

    public function valid(): bool
    {
        return $this->key() !== null;
    }

    public function rewind(): void
    {
        reset($this->values);
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->values[$offset]) || array_key_exists($offset, $this->values);
    }

    /**
     * @return T|null
     */
    #[ReturnTypeWillChange]
    public function offsetGet(mixed $offset)
    {
        if ($this->offsetExists($offset)) {
            return $this->values[$offset];
        }

        return null;
    }

    /**
     * @param int|string|null $offset
     * @param T               $value
     */
    #[ReturnTypeWillChange]
    public function offsetSet(mixed $offset, mixed $value): void
    {
        if ($offset === null) {
            $this->values[] = $value;
        } else {
            $this->values[$offset] = $value;
        }
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->values[$offset]);
    }

    public function count(): int
    {
        return count($this->values);
    }

    public function isEmpty(): bool
    {
        return empty($this->values);
    }

    /**
     * @param string $field
     * @param mixed  $value
     *
     * @return static<T>
     */
    public function with(string $field, mixed $value): self
    {
        $collection = new static();

        if ($this->isEmpty()) {
            return $collection;
        }

        $firstItem = $this->first();
        if ($firstItem === null) {
            return $collection;
        }

        $formattedField = ucfirst($field);

        if (is_bool($value)) {
            $method = 'is' . $formattedField;
            if (!method_exists($firstItem, $method)) {
                $method = 'get' . $formattedField;
            }
        } else {
            $method = 'get' . $formattedField;
        }

        if (!method_exists($firstItem, $method)) {
            throw new BadMethodCallException("Method {$method} not found on items in collection.");
        }

        foreach ($this->values as $item) {
            if (method_exists($item, $method) && $item->$method() === $value) {
                $collection->add($item);
            }
        }

        return $collection;
    }

    /**
     * @param Closure(T, int|string|null): bool $closure
     * @param int                               $mode    [optional]
     *
     * @return static<T>
     *                   <p>
     *                   Flag determining what arguments are sent to <i>callback</i>:
     *                   </p><ul>
     *                   <li>
     *                   <b>ARRAY_FILTER_USE_KEY</b> - pass key as the only argument
     *                   to <i>callback</i> instead of the value</span>
     *                   </li>
     *                   <li>
     *                   <b>ARRAY_FILTER_USE_BOTH</b> - pass both value and key as
     *                   arguments to <i>callback</i> instead of the value</span>
     *                   </li>
     *                   </ul>
     */
    public function filter(Closure $closure, int $mode = 0): self
    {
        $collection = new static();

        if ($this->isEmpty()) {
            return $collection;
        }

        $items = array_filter($this->values, $closure, $mode);

        foreach ($items as $item) {
            $collection->add($item);
        }

        return $collection;
    }

    /**
     * @return array<int|string, T>
     */
    public function asArray(): array
    {
        return $this->values;
    }

    /**
     * @return T|null
     */
    public function first(): mixed
    {
        if ($this->isEmpty()) {
            return null;
        }
        return $this->values[array_key_first($this->values)];
    }

    /**
     * @return T|null
     */
    public function last(): mixed
    {
        if ($this->isEmpty()) {
            return null;
        }
        return $this->values[array_key_last($this->values)];
    }

    public function clear(): void
    {
        $this->values = [];
    }

    /**
     * Добавляет элемент в конец коллекции.
     *
     * @param T $value
     * @return void
     */
    public function add(mixed $value): void
    {
        $this->values[] = $value;
    }

    /**
     * Возвращает первый элемент, удовлетворяющий условию.
     *
     * @param Closure(T, int|string|null): bool $closure
     * @return T|null
     */
    public function find(Closure $closure): mixed
    {
        foreach ($this->values as $key => $item) {
            if ($closure($item, $key)) {
                return $item;
            }
        }
        return null;
    }
}
