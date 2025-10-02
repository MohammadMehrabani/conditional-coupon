# conditional coupon code

With this package, you can add both simple and conditional discount coupon features to your application. You can develop your own custom conditions, which will be automatically checked when a discount coupon is applied. If a condition is violated, an error will be returned and the coupon will not be allowed to be used by the user.
## Installation

You can install the package via composer:

```bash
composer require mohammadmehrabani/conditional-coupon
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="conditional-coupon-migrations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="conditional-coupon-config"
```

This is the contents of the published config file:

```php

return [
    'conditions' => [
        // Add custom check condition classes Implemented \MohammadMehrabani\ConditionalCoupon\CheckConditionAbstract 
        // for example:
        // \App\CheckUsageLimitCondition::class => 'Usage limit',
        // \App\CheckPaymentTypesCondition::class => 'Payment type restriction',
        // \App\CheckPaymentMethodsCondition::class => 'Payment method restriction',
    ]
];
```

If you want any conditions to be checked when applying a discount coupon, simply create a class that implements `\MohammadMehrabani\ConditionalCoupon\CheckConditionAbstract` and add it to the `conditional-coupon.php` config file along with a title for the condition. No further action is needed—it's that simple.

```php
namespace App;

use App\Models\Order;
use MohammadMehrabani\ConditionalCoupon\CheckConditionAbstract;
use MohammadMehrabani\ConditionalCoupon\Exceptions\CouponException;

class CheckUsageLimitCondition extends CheckConditionAbstract
{
    /**
     * @throws CouponException|\Exception
     */
    public function handle(mixed $payload, \Closure $next): mixed
    {
        $orderCount = Order::where([ // only for example
            'coupon_id' => $this->coupon->id,
            'user_id'   => auth()->id(),
            'status'    => 'fulfilled',
        ])->count();

        if ($orderCount > $this->condition->data) {
            throw new \Exception(__('Coupon user usage limit reached.'), 400);
        }

        return $next($payload);
    }
}

class CheckPaymentTypesCondition extends CheckConditionAbstract
{
    public function handle(mixed $payload, \Closure $next): mixed
    {
        if (empty(array_intersect($this->condition->data, ['cash', 'credit']))) {
            throw new \Exception('payment types do not match.');
        }

        return $next($payload);
    }
}

```

add to config file:

```php
return [
    'conditions' => [
        \App\CheckUsageLimitCondition::class   => 'Usage limit',
        \App\CheckPaymentTypesCondition::class => 'Payment type restriction',
    ]
];
```

## Usage

Implementing the CRUD for discount coupons—and adding conditions if needed—is up to you. Once created, you can simply use the discount coupon feature for your orders as shown in the example below.

### create coupon with conditions

```php
use MohammadMehrabani\ConditionalCoupon\Models\Coupon;
use MohammadMehrabani\ConditionalCoupon\Models\CouponCondition;

$coupon = Coupon::create([
    'code' => 'AMZ100',
    // other fill columns
]);

$couponCondition = CouponCondition::create([
    'coupon_id' => $coupon->id,
    'condition' => \App\CheckUsageLimitCondition::class,
    'data' => 1
]);

$couponCondition = CouponCondition::create([
    'coupon_id' => $coupon->id,
    'condition' => \App\CheckPaymentTypesCondition::class,
    'data' => ["credit", "cash"],
]);
```

### create coupon without conditions

```php
use MohammadMehrabani\ConditionalCoupon\Models\Coupon;
use MohammadMehrabani\ConditionalCoupon\Models\CouponCondition;

$coupon = Coupon::create([
    'code' => 'AMZ100',
    // fill other columns
]);
```

### use coupon for order

```php
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use MohammadMehrabani\ConditionalCoupon\InquiryCoupon;

DB::transaction(function () {
    $inquiryCoupon = new InquiryCoupon();
    [
        $currency,
        $amount,
        $discountAmount,
        $payableAmount,
        $coupon
    ] = $inquiryCoupon->handle(code: request()->get('coupon_code'), amount: 1000000, locked: true);
   
   $order = Order::create([ // only for example
        'discount'  => $discountAmount,
        'amount'    => $payableAmount,
        'user_id'   => auth()->id(),
        'status'    => 'initial',
        'coupon_id' => $coupon->id,
        # Other fields as needed:
        // 'coupon_code'         => $coupon->code,
        // 'discount_percentage' => $coupon->discount_percentage,
        // 'discount_amount'     => $coupon->discount_amount,
    ]);
    
    $coupon->increment('used_count');
});
```

## Discount Logic

The package supports two types of discount fields:

- `discount_percentage`
- `discount_amount`

### Rules

1. **Single Field Case**
    - If only one of the fields is provided, the discount will be applied based on that field.

2. **Both Fields Case**
    - If both `discount_percentage` and `discount_amount` are provided:
        - The discount value is first calculated from the `discount_percentage`.
        - If the calculated percentage discount is **greater than** the fixed `discount_amount`, then the **fixed amount** will be applied.
        - If the calculated percentage discount is **less than or equal to** the fixed `discount_amount`, then the **percentage-based discount** will be applied.

### Example

- Order total: **$200**
- `discount_percentage`: **20%**
- `discount_amount`: **$30**

Calculation:
- 20% of $200 = $40
- Since $40 (percentage) > $30 (amount), the final discount will be **$30**.

---

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Credits

- [Mohammad Hossein Mehrabani](https://github.com/mohammadmehrabani)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
