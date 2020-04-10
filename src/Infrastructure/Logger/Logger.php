<?php

namespace App\Infrastructure\Logger;

use App\Infrastructure\Context;

final class Logger
{
    public static function emergency(Context\Context $context, string $message)
    {
        $logger = Context\Logger::fromContext($context);

        if (!$logger) {
            return;
        }

        $logger->emergency($message, $context->getKeysAndValues());
    }

    public static function alert(Context\Context $context, string $message)
    {
        $logger = Context\Logger::fromContext($context);

        if (!$logger) {
            return;
        }

        $logger->alert($message, $context->getKeysAndValues());
    }

    public static function critical(Context\Context $context, string $message)
    {
        $logger = Context\Logger::fromContext($context);

        if (!$logger) {
            return;
        }

        $logger->critical($message, $context->getKeysAndValues());
    }

    public static function error(Context\Context $context, string $message)
    {
        $logger = Context\Logger::fromContext($context);

        if (!$logger) {
            return;
        }

        $logger->error($message, $context->getKeysAndValues());
    }

    public static function warning(Context\Context $context, string $message)
    {
        $logger = Context\Logger::fromContext($context);

        if (!$logger) {
            return;
        }

        $logger->warning($message, $context->getKeysAndValues());
    }

    public static function notice(Context\Context $context, string $message)
    {
        $logger = Context\Logger::fromContext($context);

        if (!$logger) {
            return;
        }

        $logger->notice($message, $context->getKeysAndValues());
    }

    public static function info(Context\Context $context, string $message)
    {
        $logger = Context\Logger::fromContext($context);

        if (!$logger) {
            return;
        }

        $logger->info($message, $context->getKeysAndValues());
    }

    public static function debug(Context\Context $context, string $message)
    {
        $logger = Context\Logger::fromContext($context);

        if (!$logger) {
            return;
        }

        $logger->debug($message, $context->getKeysAndValues());
    }

    public static function log(Context\Context $context, string $level, string $message)
    {
        $logger = Context\Logger::fromContext($context);

        if (!$logger) {
            return;
        }

        $logger->log($level, $message, $context->getKeysAndValues());
    }
}
