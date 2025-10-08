<?php

namespace MohammadMehrabani\ConditionalCoupon\Tests\Conditions;

use MohammadMehrabani\ConditionalCoupon\CheckConditionAbstract;

class CheckPaymentTypesCondition extends CheckConditionAbstract
{
    public function handle(mixed $payload, \Closure $next): mixed
    {
        if (empty(array_intersect($this->condition->data, ['cash', 'credit']))) {
            throw new \Exception('payment types do not match.', 400);
        }

        return $next($payload);
    }
}
