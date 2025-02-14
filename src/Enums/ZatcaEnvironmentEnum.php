<?php

namespace Saleh7\Zatca\Enums;

enum ZatcaEnvironmentEnum: string
{
    case Sandbox = 'sandbox';

    case Simulation = 'simulation';

    case Production = 'production';

    /**
     * Returns the base URI for the given API environment.
     *
     * @return string The base URI corresponding to the environment.
     */
    public function getBaseUri(): string
    {
        return match($this) {
            self::Sandbox => 'https://gw-fatoora.zatca.gov.sa/e-invoicing/developer-portal',
            self::Simulation => 'https://gw-fatoora.zatca.gov.sa/e-invoicing/simulation',
            self::Production => 'https://gw-fatoora.zatca.gov.sa/e-invoicing/core',
        };
    }
}