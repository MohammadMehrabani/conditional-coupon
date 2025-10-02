<?php

namespace MohammadMehrabani\ConditionalCoupon;

use MohammadMehrabani\ConditionalCoupon\Exceptions\CouponException;

class CheckValidCouponCondition extends CheckConditionAbstract
{
    /**
     * @throws CouponException
     */
    public function handle(mixed $payload, \Closure $next): mixed
    {
        if ($this->coupon->used_count >= $this->coupon->total_count) {
            throw CouponException::couponUsageLimitReached();
        }

        if ($this->coupon->start_at->isAfter(now()) || $this->coupon->end_at->isBefore(now())) {
            throw CouponException::couponTimeLimitReached();
        }

        return $next($payload);
    }
}
