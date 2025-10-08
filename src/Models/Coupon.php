<?php

namespace MohammadMehrabani\ConditionalCoupon\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use MohammadMehrabani\ConditionalCoupon\Database\Factories\CouponFactory;
use MohammadMehrabani\ConditionalCoupon\Enums\CouponStatusEnum;
use MohammadMehrabani\ConditionalCoupon\Enums\CurrencyEnum;

class Coupon extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'code',
        'discount_percentage',
        'discount_amount',
        'currency',
        'total_count',
        'used_count',
        'start_at',
        'end_at',
        'provider',
        'status',
        'description',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'currency' => CurrencyEnum::class,
        'status' => CouponStatusEnum::class,
    ];

    protected static function newFactory(): Factory
    {
        return CouponFactory::new();
    }

    public function conditions(): HasMany
    {
        return $this->hasMany(CouponCondition::class);
    }
}
