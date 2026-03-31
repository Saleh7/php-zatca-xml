<?php
require __DIR__.'/../../vendor/autoload.php';

use Saleh7\Zatca\CertificateBuilder;
use Saleh7\Zatca\Exceptions\CertificateBuilderException;

// Usage example matching ZATCA SDK (csr-config-example-EN.properties):
try {
    (new CertificateBuilder)
        ->setOrganizationIdentifier('399999999900003')
        ->setSerialNumber('TST', 'TST', 'ed22f1d8-e6a2-1118-9b58-d9a8f11e445f')
        ->setCommonName('TST-886431145-399999999900003')
        ->setCountryName('SA')
        ->setOrganizationName('Maximum Speed Tech Supply LTD')
        ->setOrganizationalUnitName('Riyadh Branch')
        ->setAddress('RRRD2929')
        // 4-digit bitmask: [Standard, Simplified, future, future]
        ->setInvoiceType('1100')
        // ENV_PRODUCTION, ENV_NONPROD (sandbox), or ENV_SIMULATION
        ->setEnvironment(CertificateBuilder::ENV_SIMULATION)
        ->setBusinessCategory('Supply activities')
        ->generateAndSave('output/certificate.csr', 'output/private.pem');

    echo "Certificate and private key saved.\n";
} catch (CertificateBuilderException $e) {
    echo 'Error: '.$e->getMessage()."\n";
    exit(1);
}
