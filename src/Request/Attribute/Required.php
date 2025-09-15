<?php

declare (strict_types=1);

namespace Kosmosafive\Bitrix\DS\Request\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
class Required
{
}
