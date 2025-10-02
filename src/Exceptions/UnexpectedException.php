<?php

namespace MohammadMehrabani\ConditionalCoupon\Exceptions;

use Throwable;
use Exception;

class UnexpectedException extends Exception
{
    public function __construct($message = 'Unexpected error occurred!', $code = 500, ?Throwable $previous = null)
    {
        if (!is_null($previous)) {
            report($previous);
        }

        parent::__construct(__($message), $code, $previous);
    }

    public static function build(string $message = ''): static
    {
        return new static($message ?: 'Unexpected error occurred!');
    }
}
