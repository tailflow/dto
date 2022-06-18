<?php

declare(strict_types=1);

namespace Tailflow\DataTransferObjects\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use JsonException;
use Tailflow\DataTransferObjects\DataTransferObject;
use Tailflow\DataTransferObjects\DataTransferObjectCollection;

class DataTransferObjectCollectionCast implements CastsAttributes
{
    protected string $class;

    public function __construct(string $class)
    {
        $this->class = $class;
    }

    /**
     * @param Model $model
     * @param string $key
     * @param string|null $value
     * @param array $attributes
     * @return DataTransferObjectCollection|null
     * @throws JsonException
     */
    public function get(
        $model,
        string $key,
        mixed $value,
        array $attributes
    ): DataTransferObjectCollection|null {
        if (is_null($value)) {
            return null;
        }

        return new $this->class(
            json_decode($value, true, 512, JSON_THROW_ON_ERROR)
        );
    }

    /**
     * @param Model $model
     * @param string $key
     * @param array|Collection|DataTransferObjectCollection $value
     * @param array $attributes
     * @return string|null
     * @throws JsonException
     */
    public function set($model, string $key, $value, array $attributes): ?string
    {
        if (is_null($value)) {
            return null;
        }

        if (is_array($value)) {
            $value = new $this->class($value);
        }

        if ($value instanceof Collection) {
            $value = new $this->class($value->toArray());
        }

        if (!$value instanceof $this->class) {
            throw new InvalidArgumentException("Value must be of type [$this->class], Collection, array, or null");
        }

        return json_encode($value, JSON_THROW_ON_ERROR);
    }
}
