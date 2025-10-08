<?php

namespace MohammadMehrabani\ConditionalCoupon\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use MohammadMehrabani\ConditionalCoupon\Database\Factories\CouponConditionFactory;

class CouponCondition extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'coupon_id',
        'condition',
        'data',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    protected static function newFactory(): Factory
    {
        return CouponConditionFactory::new();
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }
}
