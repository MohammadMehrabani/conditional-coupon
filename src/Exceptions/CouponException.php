<?php

namespace MohammadMehrabani\ConditionalCoupon\Exceptions;

use Exception;

class CouponException extends Exception
{
    public function __construct($message = 'The process could not be done', $code = 400)
    {
        parent::__construct($message, $code);
    }

    public function report(): bool
    {
        return false;
    }

    public static function couponNotExists(): self
    {
        return new self('Coupon does not exist.');
    }

    public static function orderIsFree(): self
    {
        return new self('The order is a free.');
    }

    public static function couponUsageLimitReached(): self
    {
        return new self(__('Coupon usage limit reached.'));
    }

    public static function couponTimeLimitReached(): self
    {
        return new self(__('Coupon time limit reached.'));
    }
}
