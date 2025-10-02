<?php

namespace MohammadMehrabani\ConditionalCoupon;

use MohammadMehrabani\ConditionalCoupon\Models\Coupon;
use MohammadMehrabani\ConditionalCoupon\Models\CouponCondition;

abstract class CheckConditionAbstract
{
    public function __construct(
        protected Coupon $coupon,
        protected CouponCondition $condition,
        protected int $amount,
    ) {}

    abstract public function handle(mixed $payload, \Closure $next): mixed;
}
