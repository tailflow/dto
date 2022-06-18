<?php

declare(strict_types=1);

namespace Tailflow\DataTransferObjects;

use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use Tailflow\DataTransferObjects\Casts\DataTransferObjectCollectionCast;

abstract class DataTransferObjectCollection extends Collection implements Castable
{
    abstract public static function getItemClass(): string;

    /**
     * @param array $items
     * @throws InvalidArgumentException
     */
    public function __construct(array $items = [])
    {
        $itemClass = static::getItemClass();

        $items = array_map(
            fn(mixed $item) => is_array($item) ? new $itemClass($item) : $item,
            $items
        );

        parent::__construct($items);
    }

    public static function castUsing(array $arguments): DataTransferObjectCollectionCast
    {
        return new DataTransferObjectCollectionCast(static::class);
    }
}
