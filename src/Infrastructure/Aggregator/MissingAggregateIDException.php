<?php

namespace App\Infrastructure\Aggregator;

use LogicException;
use Throwable;

final class MissingAggregateIDException extends LogicException
{
    public function __construct($message = '', $code = 0, Throwable $previous = null)
    {
        $message = $message ?? 'Aggregate ID must be provided.';

        parent::__construct($message, $code, $previous);
    }
}
