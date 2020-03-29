<?php

namespace App\Infrastructure\Exception;

use Symfony\Component\Translation\Exception\NotFoundResourceException;
use Throwable;

class NotFoundException extends NotFoundResourceException
{
    public function __construct($message = "", array $context = [], $code = 0, Throwable $previous = null)
    {
        parent::__construct($this->buildMessage($message, $context), $code, $previous);
    }

    private function buildMessage(string $message, array $context = []): string
    {
        if (empty($context)) {
            return $message;
        }

        $messageContext = [];
        foreach ($context as $key => $value) {
            $messageContext[] = "$key:$value";
        }

        return \sprintf('%s [%s]', $message, \implode($messageContext, ', '));
    }
}
