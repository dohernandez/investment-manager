<?php

namespace App\Infrastructure\Logger;

use App\Infrastructure\Context;

final class Logger
{
    public function emergency(Context\Context $context, string $message)
    {
        $logger = Context\Logger::fromContext($context);

        if (!$logger) {
            return;
        }

        $logger->emergency($message, $context->getKeysAndValues());
    }

    public function alert(Context\Context $context, string $message)
    {
        $logger = Context\Logger::fromContext($context);

        if (!$logger) {
            return;
        }

        $logger->alert($message, $context->getKeysAndValues());
    }

    public function critical(Context\Context $context, string $message)
    {
        $logger = Context\Logger::fromContext($context);

        if (!$logger) {
            return;
        }

        $logger->critical($message, $context->getKeysAndValues());
    }

    public function error(Context\Context $context, string $message)
    {
        $logger = Context\Logger::fromContext($context);

        if (!$logger) {
            return;
        }

        $logger->error($message, $context->getKeysAndValues());
    }

    public function warning(Context\Context $context, string $message)
    {
        $logger = Context\Logger::fromContext($context);

        if (!$logger) {
            return;
        }

        $logger->warning($message, $context->getKeysAndValues());
    }

    public function notice(Context\Context $context, string $message)
    {
        $logger = Context\Logger::fromContext($context);

        if (!$logger) {
            return;
        }

        $logger->notice($message, $context->getKeysAndValues());
    }

    public function info(Context\Context $context, string $message)
    {
        $logger = Context\Logger::fromContext($context);

        if (!$logger) {
            return;
        }

        $logger->info($message, $context->getKeysAndValues());
    }

    public function debug(Context\Context $context, string $message)
    {
        $logger = Context\Logger::fromContext($context);

        if (!$logger) {
            return;
        }

        $logger->debug($message, $context->getKeysAndValues());
    }

    public function log(Context\Context $context, string $level, string $message)
    {
        $logger = Context\Logger::fromContext($context);

        if (!$logger) {
            return;
        }

        $logger->log($level, $message, $context->getKeysAndValues());
    }
}
