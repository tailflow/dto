<?php

declare(strict_types=1);

namespace Tailflow\DataTransferObjects;

use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Facades\Validator as ValidatorFacade;
use Illuminate\Validation\Validator;
use InvalidArgumentException;
use JsonSerializable;
use ReflectionClass;
use ReflectionProperty;
use Tailflow\DataTransferObjects\Casts\DataTransferObjectCast;

abstract class DataTransferObject implements Castable, Arrayable, JsonSerializable
{
    protected Validator $validator;

    /**
     * @param array $attributes
     * @param bool $partial
     * @throws InvalidArgumentException
     */
    public function __construct(array $attributes = [], bool $partial = true)
    {
        $this->validator = ValidatorFacade::make([], []);

        $this->fill($attributes, $partial);
    }

    /**
     * @param array $attributes
     * @param bool $partial
     * @return $this
     * @throws InvalidArgumentException
     */
    public function fill(array $attributes, bool $partial = true): static
    {
        $reflection = new ReflectionClass($this);
        $properties = $reflection->getProperties(ReflectionProperty::IS_PUBLIC);

        $missingAttributes = [];

        foreach ($properties as $property) {
            if (!array_key_exists($property->getName(), $attributes)) {
                $missingAttributes[] = $property->getName();
                continue;
            }

            $value = $attributes[$property->getName()];

            $propertyType = (string) $property->getType();

            if (class_exists($propertyType)) {
                $value = new $propertyType($value);
            }

            $this->{$property->getName()} = $value;
        }

        if (!$partial && count($missingAttributes)) {
            throw new InvalidArgumentException(
                'The payload for '.static::class.' DTO is missing the following attributes: '.implode(
                    ', ',
                    $missingAttributes
                )
            );
        }

        return $this;
    }

    public function toArray(array $overrides = []): array
    {
        $reflection = new ReflectionClass($this);
        $properties = $reflection->getProperties(ReflectionProperty::IS_PUBLIC);

        $attributes = collect($properties)->mapWithKeys(
            function (ReflectionProperty $property) {
                if (!$property->isInitialized($this) || !$property->isDefault()) {
                    return [];
                }

                $value = $property->getValue($this);

                return [$property->getName() => $value instanceof Arrayable ? $value->toArray() : $value];
            }
        )->toArray();

        return array_merge($attributes, $overrides);
    }

    public static function castUsing(array $arguments): DataTransferObjectCast
    {
        return new DataTransferObjectCast(static::class);
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
