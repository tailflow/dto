# Laravel Data Transfer Object

[![Latest Version on Packagist](https://img.shields.io/packagist/v/tailflow/dto.svg)](https://packagist.org/packages/tailflow/dto)
[![Build Status on GitHub Actions](https://img.shields.io/github/actions/workflow/status/tailflow/dto/ci.yml?branch=main)](https://github.com/tailflow/dto/actions)

A simple and lightweight implementation of Data Transfer Objects (DTO) in Laravel with optional casting support.

Under the hood it implements Laravel's [`Castable`](https://laravel.com/docs/8.x/eloquent-mutators#castables) interface with a Laravel [custom cast](https://laravel.com/docs/7.x/eloquent-mutators#custom-casts) that handles serializing between the `DataTransferObject` (or a compatible array) and your JSON database column.

## Installation

You can install the package via composer:

```bash
composer require tailflow/dto
```

## Usage

### 1. Create your `DataTransferObject` or `DataTransferObjectCollection`


```php
namespace App\DataTransferObjects;

use Tailflow\DataTransferObjects\DataTransferObject;

class Address extends DataTransferObject
{
    public string $country;
    public string $city;
    public string $street;
}
```

```php
namespace App\DataTransferObjects;

use Tailflow\DataTransferObjects\DataTransferObjectCollection;

class WorkAddresses extends DataTransferObjectCollection 
{
    public static function getItemClass(): string
    {
        return Address::class;
    }
}
```

### (Optional) 2. Configure your Eloquent attribute casting:

Note that this should be a `jsonb` or `json` column in your database schema.

```php
namespace App\Models;

use App\DataTansferObjects\Address;
use App\DataTansferObjects\WorkAddresses;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $casts = [
        'address' => Address::class,
        'work_addresses' => WorkAddresses::class,
    ];
}
```

### 3. Enjoy ~

Pass DTOs as arguments or use as return values, and get a nice autocompletion.

```php
function getAddress(Address $originalAddress): Address 
{
    $address = new Address();
    $address->county = $originalAddress->country;
    $address->city = 'Tokyo';
    $address->street = '4-2-8 Shiba-koen';
  
    return $address;
}

// or

function getAddress(): Address 
{
    return new Address(
        [
            'country' => 'Japan',
            'city' => 'Tokyo',
            'street' => '4-2-8 Shiba-koen',
        ]
    );
}
```

On Eloquent models, you can now pass either an instance of your `Address` class, or even just an array with a compatible structure. It will automatically be cast between your class and JSON for storage and the data will be validated on the way in and out.

```php
$workAddress = new Address();
$workAddress->country = 'Japan';
$workAddress->city = 'Osaka';

$user = User::create([
    // ...
    'address' => [
        'country' => 'Japan',
        'city' => 'Tokyo',
        'street' => '4-2-8 Shiba-koen',
    ],
    'work_addresses' => [
        $workAddress
    ]

]);

$residents = User::where('address->city', 'Tokyo')->get();
```

But the best part is that you can decorate your class with domain-specific methods to turn it into a powerful value object.

```php
$user->address->toMapUrl();

$user->address->getCoordinates();

$user->address->getPostageCost($sender);

$user->address->calculateDistance($otherUser->address);

echo (string) $user->address;
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
