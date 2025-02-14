<?php

namespace Saleh7\Zatca\Exceptions;

use Exception;
use Throwable;

class ZatcaApiException extends ZatcaException
{
    protected string $defaultMessage = 'Zatca API request failed.';
}