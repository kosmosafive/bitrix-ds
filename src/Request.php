<?php

declare(strict_types=1);

namespace Kosmosafive\Bitrix\DS;

use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Kosmosafive\Bitrix\DS\Request\Attribute\Required;
use Kosmosafive\Bitrix\Localization\Loc;
use Ramsey\Uuid\Uuid;
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

    public function filterInteger($value): ?int
    {
        return filter_var($value, FILTER_VALIDATE_INT, [
            'flags' => FILTER_NULL_ON_FAILURE,
            'options' => [],
        ]);
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

    public function filterFloat($value): ?float
    {
        $value = trim(str_replace(',', '.', (string) $value));

        return filter_var($value, FILTER_VALIDATE_FLOAT, [
            'flags' => FILTER_NULL_ON_FAILURE,
            'options' => [],
        ]);
    }

    public function filterBoolean($value): bool
    {
        return ($value === 'true') || ((int) $value === 1) || ($value === 'on') || ($value === 'Y');
    }

    public function filterUuid($value): ?string
    {
        $value = (string) $value;
        return Uuid::isValid($value) ? $value : null;
    }

    public function validate(): Result
    {
        $result = new Result();

        $reflectionClass = new ReflectionClass(static::class);
        $properties = $reflectionClass->getProperties();

        Loc::loadMessages($reflectionClass->getFileName());

        foreach ($properties as $property) {
            $requestAttribute = current($property->getAttributes(Required::class));
            if (!$requestAttribute) {
                continue;
            }

            $requestAttributeInstance = $requestAttribute->newInstance();

            $value = $property->getValue($this);
            $errorCodeMethod = 'getFormFieldCodeFor' . ucfirst($property->getName());

            if ($reflectionClass->hasMethod($errorCodeMethod)) {
                $getFormCodeMethod = $reflectionClass->getMethod($errorCodeMethod);
                $errorCode = $getFormCodeMethod->invoke($this);
            } else {
                $errorCode = 'required';
            }

            if (
                is_null($value)
                || (is_array($value) && empty($value))
            ) {
                if ($requestAttributeInstance->key) {
                    $message = Loc::getMessage(
                        'REQUEST_ERROR_FIELD_WITH_KEY_REQUIRED',
                        ['#KEY#' => $requestAttributeInstance->key]
                    );
                } else {
                    $message = Loc::getMessage('REQUEST_ERROR_FIELD_REQUIRED');
                }

                if (empty($message)) {
                    $message = 'Field "' . $property->getName() . '" is required';
                }

                $result->addError(
                    new Error(
                        $message,
                        $errorCode,
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
