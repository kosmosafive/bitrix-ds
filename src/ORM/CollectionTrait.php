<?php

declare(strict_types=1);

namespace Kosmosafive\Bitrix\DS\ORM;

use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\Type\Collection;

trait CollectionTrait
{
    /**
     * @param string $field
     * @param        $value
     *
     * @return $this
     */
    public function with(string $field, $value): static
    {
        $collection = new static();

        foreach ($this as $obj) {
            if ($obj->get($field) !== $value) {
                continue;
            }

            $collection->add($obj);
        }

        return $collection;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $arr = [];

        foreach ($this as $obj) {
            $arr[] = (method_exists($obj, 'toArray')) ? $obj->toArray() : $obj->collectValues();
        }

        return $arr;
    }

    /**
     * You can use short mode: ORMCollection::sort('field'); This is equal ORMCollection::sort(['field' => SORT_ASC]).
     *
     * @param string|array $columns
     *
     * @return $this
     *
     * @throws ArgumentOutOfRangeException
     */
    public function sort(string|array $columns): static
    {
        Collection::sortByColumn($this->_objects, $columns);

        return $this;
    }

    /**
     * @param array $order
     *
     * @return $this
     *
     * @throws ArgumentOutOfRangeException
     */
    public function sortByOrder(array $order): static
    {
        $columns = [];
        foreach ($order as $key => $value) {
            if (is_numeric($key)) {
                $columns[$value] = SORT_ASC;
                continue;
            }

            $sortDirectionList = [];
            foreach (explode(',', $value) as $sortDirection) {
                $sortDirection = strtoupper(trim($sortDirection));

                switch ($sortDirection) {
                    case 'ASC':
                        $sortDirectionList[] = SORT_ASC;
                        break;
                    case 'DESC':
                        $sortDirectionList[] = SORT_DESC;
                        break;
                    default:
                        continue 2;
                }
            }

            if (empty($sortDirectionList)) {
                continue;
            }

            $columns[$key] = $sortDirectionList;
        }

        return $this->sort($columns);
    }
}
