<?php

namespace MohammadMehrabani\ConditionalCoupon;

class GetCustomConditions
{
    public static function handle()
    {
        $conditionItems = [];

        foreach (config('conditional-coupon.conditions') as $condition => $translate) {
            $conditionItems[$condition] = __($translate);
        }

        return $conditionItems;
    }
}
