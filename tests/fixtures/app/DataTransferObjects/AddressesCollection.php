<?php

declare(strict_types=1);

namespace Tailflow\DataTransferObjects\Tests\Fixtures\App\DataTransferObjects;

use Tailflow\DataTransferObjects\DataTransferObjectCollection;

class AddressesCollection extends DataTransferObjectCollection
{
    public static function getItemClass(): string
    {
        return Address::class;
    }
}
