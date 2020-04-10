<?php

namespace Gedmo\Exception;

use Gedmo\Exception;

/**
 * InvalidCastingException
 *
 * Triggered when mapping key value is not
 * valid type or incomplete.
 *
 */
class InvalidCastingException
    extends InvalidArgumentException
    implements Exception
{
}
