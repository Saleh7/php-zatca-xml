<?php

namespace Saleh7\Zatca\Exceptions;

/**
 * Class ZatcaApiException
 *
 * Exception thrown for API communication errors.
 */
class ZatcaApiException extends ZatcaException
{
    protected string $defaultMessage = 'Zatca API request failed.';
}
