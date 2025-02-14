<?php
require __DIR__ . '/../vendor/autoload.php';

use Saleh7\Zatca\CertificateBuilder;
use Saleh7\Zatca\CertificateBuilderException;


// Usage example:
try {
    (new CertificateBuilder())
        ->setOrganizationIdentifier('312345678901233') // The Organization Identifier must be 15 digits, starting andending with 3
        // string $solutionName .. The solution provider name
        // string $model .. The model of the unit the stamp is being generated for
        // string $serialNumber .. # If you have multiple devices each should have a unique serial number
        ->setSerialNumber('Saleh', '1n', 'SME00023')
        ->setCommonName('My Organization') // The common name to be used in the certificate
        ->setCountryName('SA') // The Country name must be Two chars only
        ->setOrganizationName('My Company') // The name of your organization
        ->setOrganizationalUnitName('IT Department') // A subunit in your organizatio
        ->setAddress('Riyadh 1234 Street') // like Riyadh 1234 Street 
        ->setInvoiceType(1100)// # Four digits, each digit acting as a bool. The order is as follows: Standard Invoice, Simplified, future use, future use 
        ->setProduction(false)// true = Production |  false = Testing
        ->setBusinessCategory('Technology') // Your business category like food, real estate, etc
        
        ->generateAndSave('output/certificate.csr', 'output/private.pem');
        
    echo "Certificate and private key saved.\n";
} catch (CertificateBuilderException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
