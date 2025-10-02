<?php

namespace MohammadMehrabani\ConditionalCoupon\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CouponCondition extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'coupon_id',
        'condition',
        'data',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }
}
