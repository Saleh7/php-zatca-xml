<?php

namespace Saleh7\Zatca\Exceptions;

use Exception;
use Throwable;

class ZatcaException extends Exception
{
    protected string $defaultMessage = '';

    protected array $context = [];

    public function __construct(?string $message = null, array $context = [], int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message ?? $this->getDefaultMessage(), $code, $previous);
        $this->context = $context;
    }

    public function withContext(array $context): self
    {
        $this->context = array_merge($this->context, $context);

        return $this;
    }

    public function getContext(): array
    {
        return $this->context;
    }

    public function getDefaultMessage(): string
    {
        return $this->defaultMessage;
    }
}