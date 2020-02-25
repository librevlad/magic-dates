# Magic Dates

Adds magic methods to your Eloquent query builder to work with dates.

## Installation

You can install the package via composer:

```bash
composer require librevlad/magic-dates
```

## Usage

Works only with fields explicitly declared on the $dates property of your model.

```php

class Order extends Model {

    protected $dates = [
        'created_at',
        'updated_at',
        'shipped_at',
    ];
  
    /**  **/

}

```

```php

   // same thing as ->where('created_at','>', now()->subWeek() )
   $orders = Order::createdSince( now()->subWeek() );

   // same thing as ->where('updated_at','>', now()->subWeek() )
   $orders = Order::updatedAfter( now()->subWeek() );

   // same thing as ->whereBetween('shipped_at', now()->subWeek(), now() )
   $orders = Order::shippedBetween( now()->subYear(), now() );

```

## Testing

```bash
composer test
```

## License

The MIT License (MIT).
