<?php

namespace App\Infrastructure\Exception;

use InvalidArgumentException;

/**
 * InvalidCastingException
 *
 * Triggered when mapping key value is not
 * valid type or incomplete.
 *
 */
class InvalidCastingException extends InvalidArgumentException
{
}
