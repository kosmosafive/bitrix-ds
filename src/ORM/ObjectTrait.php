<?php

declare(strict_types=1);

namespace Kosmosafive\Bitrix\DS\ORM;

use Bitrix\Main\Engine\Response\Converter;

/**
 * @method collectValues(): array
 */
trait ObjectTrait
{
    public function toArray(): array
    {
        $arItem = (new Converter(Converter::OUTPUT_JSON_FORMAT))->process($this->collectValues());

        foreach ($arItem as $key => $value) {
            if (!is_object($value)) {
                continue;
            }

            if (method_exists($value, 'toArray')) {
                $arItem[$key] = $value->toArray();
            } elseif (method_exists($value, 'collectValues')) {
                $arItem[$key] = $value->collectValues();
            }
        }

        return $arItem;
    }
}
