<?php

namespace App\Infrastructure\Context;

use App\Infrastructure\Exception\InvalidCastingException;
use Psr\Log\LoggerInterface;

final class Logger
{
    private const CONTEXT_KEY = 'logger';

    public static function fromContext(Context $context): ?LoggerInterface
    {
        $logger = $context->getValue(self::CONTEXT_KEY);

        if (!$logger) {
            return null;
        }

        if (!$logger instanceof LoggerInterface) {
            throw new InvalidCastingException('invalid logger type');
        }

        return $logger;
    }

    public static function toContext(Context $context, LoggerInterface $logger): Context
    {
        return $context->setValue(self::CONTEXT_KEY, $logger);
    }
}
