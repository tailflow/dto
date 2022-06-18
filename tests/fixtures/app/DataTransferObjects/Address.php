<?php

declare(strict_types=1);

namespace Tailflow\DataTransferObjects\Tests\Fixtures\App\DataTransferObjects;

use Illuminate\Validation\Rule;
use Tailflow\DataTransferObjects\DataTransferObject;

class Address extends DataTransferObject
{
    public string $country;
    public string $city;
    public string $street;

    public function rules(): array
    {
        return [
            'country' => [Rule::in(['jp', 'ca'])],
        ];
    }
}
