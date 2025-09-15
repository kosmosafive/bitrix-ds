<?php

declare(strict_types=1);

namespace Kosmosafive\Bitrix\DS;

use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Kosmosafive\Bitrix\Localization\Loc;
use ReflectionClass;

abstract readonly class Request
{
    public function filterString($value, bool $saveBreaks = false): ?string
    {
        if (!$value) {
            return null;
        }

        $filteredValue = strip_tags($value);
        $flags = ($saveBreaks) ? FILTER_NULL_ON_FAILURE : FILTER_NULL_ON_FAILURE | FILTER_FLAG_STRIP_LOW;
        $filteredValue = filter_var($filteredValue, FILTER_SANITIZE_FULL_SPECIAL_CHARS, ['flags' => $flags]);
        $regex = '/[^\p{Cyrillic}\p{Latin}\p{Common}]/u';
        $filteredValue = preg_replace($regex, '', $filteredValue);
        return trim($filteredValue);
    }

    public function filterEmail($value): ?string
    {
        return check_email($value, true) ? mb_strtolower(trim($value)) : null;
    }

    public function filterPositiveInteger($value): ?int
    {
        return filter_var($value, FILTER_VALIDATE_INT, [
            'flags' => FILTER_NULL_ON_FAILURE,
            'options' => [
                'min_range' => 1,
            ],
        ]);
    }

    public function filterBoolean($value): bool
    {
        return ($value === 'true') || ((int) $value === 1) || ($value === 'on') || ($value === 'Y');
    }

    public function validate(): Result
    {
        $result = new Result();

        $reflectionClass = new ReflectionClass(static::class);
        $properties = $reflectionClass->getProperties();

        Loc::loadMessages($reflectionClass->getFileName());

        foreach ($properties as $property) {
            if (!(current($property->getAttributes(Request\Attribute\Required::class)))) {
                continue;
            }

            $value = $property->getValue($this);

            if (
                is_null($value)
                || (is_array($value) && empty($value))
            ) {
                $propertyName = Loc::getMessage('REQUEST_PROPERTY_' . $property->getName()) ?: $property->getName();

                $message = Loc::getMessage(
                    'REQUEST_ERROR_FIELD_REQUIRED',
                    ['#PROPERTY#' => $propertyName]
                );
                if (empty($message)) {
                    $message = 'Field "' . $propertyName . '" is required';
                }

                $result->addError(
                    new Error(
                        $message,
                        'required',
                        [
                            'property' => $property->getName(),
                            'error' => 'required',
                        ]
                    )
                );
            }
        }

        return $result;
    }
}
