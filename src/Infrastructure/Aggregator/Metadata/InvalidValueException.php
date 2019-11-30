<?php

namespace App\Infrastructure\Aggregator\Metadata;

use Throwable;

class InvalidValueException extends \LogicException
{
    public function __construct($message = '', $code = 0, Throwable $previous = null)
    {
        if ($message === '') {
            $message = 'Invalid value type';
        }

        parent::__construct($message, $code, $previous);
    }

}
