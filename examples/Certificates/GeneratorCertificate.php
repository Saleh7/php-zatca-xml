<?php
require __DIR__ . '/../../vendor/autoload.php';

use Saleh7\Zatca\CertificateBuilder;
use Saleh7\Zatca\Exceptions\CertificateBuilderException;


// Usage example with random data:
try {
    (new CertificateBuilder())
        // The Organization Identifier must be 15 digits, starting andending with 3
        ->setOrganizationIdentifier('399999999900003')
        // string $solutionName .. The solution provider name
        // string $model .. The model of the unit the stamp is being generated for
        // string $serialNumber .. # If you have multiple devices each should have a unique serial number
        ->setSerialNumber('POS', 'A1', '98765')
        ->setCommonName('مؤسسة وقت الاستجابة')          // The common name to be used in the certificate
        ->setCountryName('SA')                          // The Country name must be Two chars only
        ->setOrganizationName('مؤسسة وقت الاستجابة')    // The name of your organization
        ->setOrganizationalUnitName('IT Department')    // Organizational unit
        ->setAddress('1234 Main St, Riyadh')            // Address
        // # Four digits, each digit acting as a bool. The order is as follows: Standard Invoice, Simplified, future use, future use 
        ->setInvoiceType(1000)
        ->setProduction(false)                          // true = Production |  false = Testing
        ->setBusinessCategory('Technology')             // Your business category like food, real estate, etc
        // Generate and save the certificate and private key
        ->generateAndSave('output/certificate.csr', 'output/private.pem');
        
    echo "Certificate and private key saved.\n";
} catch (CertificateBuilderException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}