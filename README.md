# Структуры данных

## Collection

Типизированная коллекция

При использовании статических анализаторов (например, Psalm, PHPStan) можно использовать только комментарии.
Если необходимо ограничивать выполнение кода в runtime, необходимо явно выбрасывать исключение.

Ниже приведен пример реализации типизированной коллекции, метод add.

```php
use Kosmosafive\Bitrix\DS\Collection;
use InvalidArgumentException;

class Entity
{

}

/**
 * @template-extends Collection<Entity>
 */
class EntityCollection extends Collection
{
    /**
     * @param Entity $value
     * @return EntityCollection
     */
    public function add(mixed $value): EntityCollection
    {
        if (!$value instanceof Entity) {
            throw new InvalidArgumentException("This collection only accepts instances of " . Entity::class);
        }
    
        return parent::add($value);
    }
}
```

## Request

Используется для фильтрации и валидации данные от клиента.

```php
use Kosmosafive\Bitrix\DS\Request;

readonly class GetRequest extends Request
{
    #[Required]
    protected ?Uuid $id;
    
    public function __construct(\Bitrix\Main\Request $httpRequest)
    {
        $this->id = $this->filterUuid($httpRequest->get('id'));
    }
    
    public function getId(): ?Uuid
    {
        return $this->id;
    }
}

$getRequest = new GetRequest($this->getRequest());
$validateResult = $getRequest->validate();
```