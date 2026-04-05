<p align="center">
    <img src="https://i.imgur.com/hLSMzHU.png" alt="php-zatca-xml">
</p>

<p align="center">
<img src="https://badgen.net/packagist/php/saleh7/php-zatca-xml" alt="php Version">
<a href="https://packagist.org/packages/saleh7/php-zatca-xml"><img alt="Packagist Stars" src="https://img.shields.io/packagist/stars/saleh7/php-zatca-xml"></a>
<a href="https://packagist.org/packages/saleh7/php-zatca-xml"><img alt="Packagist Downloads" src="https://img.shields.io/packagist/dt/saleh7/php-zatca-xml"></a>
<a href="https://packagist.org/packages/saleh7/php-zatca-xml"><img alt="Packagist Version" src="https://img.shields.io/packagist/v/saleh7/php-zatca-xml"></a>
<a href="https://packagist.org/packages/saleh7/php-zatca-xml"><img alt="License" src="https://img.shields.io/badge/License-MIT-yellow.svg"></a>
</p>

<p align="center">
<a href="https://github.com/Saleh7/php-zatca-xml/tree/main/examples">View Examples</a> · <a href="https://github.com/Saleh7/php-zatca-xml/issues">Report a bug</a> · <a href="https://github.com/Saleh7/php-zatca-xml/discussions">Discussions</a>
</p>

> ✅ **Production Tested** — Full onboarding flow (CSR → Compliance → 6 Invoice Types → Production Certificate → Invoice Reporting) successfully tested against ZATCA production API on **March 31, 2026** using **Ubuntu 24.04 / PHP 8.4**.

## Introduction

**PHP-ZATCA-XML** is a PHP library for generating ZATCA-compliant e-invoices (Fatoora). It handles the full lifecycle: certificate generation, XML invoice creation, digital signing, QR codes, and API submission to ZATCA.

Built to match the official **ZATCA Java SDK (R3.4.8)** specifications: secp256k1 keys, SHA256withECDSA signatures, UBL 2.1 XML, and all three environment tiers.

## Features

- **Certificate Builder** aligned with ZATCA SDK (CSR generation, secp256k1, proper DN order, Arabic support)
- **Invoice Generation** for Standard and Simplified invoices (Invoice, Credit Note, Debit Note)
- **Digital Signing** with XAdES-BES enveloped signatures
- **QR Code Generation** per ZATCA TLV specification
- **Full API Client** covering all 6 ZATCA endpoints (compliance, production, reporting, clearance, renewal)
- **Response Objects** with typed accessors for validation results, warnings, and errors
- **Data Mapper** to build invoices from arrays/JSON (e-commerce integration)

## Requirements

- PHP 8.1+
- Extensions: `ext-dom`, `ext-openssl`, `ext-hash`, `ext-mbstring`

## Installation

```bash
composer require saleh7/php-zatca-xml
```

## Quick Start: Production Onboarding

The full ZATCA onboarding flow in 4 steps:

### Step 1: Generate CSR + Private Key

> 📄 Full example: [`examples/Certificates/GeneratorCertificate.php`](examples/Certificates/GeneratorCertificate.php)

```php
use Saleh7\Zatca\CertificateBuilder;

(new CertificateBuilder)
    ->setOrganizationIdentifier('3XXXXXXXXXXXXX3')   // 15-digit VAT number
    ->setSerialNumber('ERP', '1.0', 'unique-device-uuid')
    ->setCommonName('ERP-886431145-3XXXXXXXXXXXXX3')
    ->setCountryName('SA')
    ->setOrganizationName('Your Company Name')       // Arabic supported
    ->setOrganizationalUnitName('Branch Name')
    ->setAddress('RRRD2929')
    ->setInvoiceType('1100')                         // Standard + Simplified
    ->setEnvironment(CertificateBuilder::ENV_PRODUCTION)
    ->setBusinessCategory('Supply activities')
    ->generateAndSave('output/certificate.csr', 'output/private.pem');
```

**Environment options:**
- `ENV_PRODUCTION` → `ZATCA-Code-Signing` (live)
- `ENV_NONPROD` → `TSTZATCA-Code-Signing` (sandbox/test)
- `ENV_SIMULATION` → `PREZATCA-Code-Signing` (pre-production)

### Step 2: Request Compliance Certificate (CCSID)

> 📄 Full example: [`examples/Certificates/RequestComplianceCertificate.php`](examples/Certificates/RequestComplianceCertificate.php)

Get an OTP from [fatoora.zatca.gov.sa](https://fatoora.zatca.gov.sa), then:

```php
use Saleh7\Zatca\ZatcaAPI;

$api = new ZatcaAPI('production'); // or 'sandbox', 'simulation'
$csr = file_get_contents('output/certificate.csr');

$result = $api->requestComplianceCertificate($csr, $otp);

$api->saveToJson(
    $result->getCertificate(),
    $result->getSecret(),
    $result->getRequestId(),
    'output/compliance_credentials.json'
);
```

### Step 3: Compliance Check (6 Invoice Types)

> 📄 Full automated example: [`examples/Certificates/ComplianceCheck.php`](examples/Certificates/ComplianceCheck.php)

Submit 6 test invoices to pass compliance validation:

```php
$creds = json_decode(file_get_contents('output/compliance_credentials.json'), true);

$response = $api->validateInvoiceCompliance(
    $creds['certificate'],
    $creds['secret'],
    $signedInvoiceXml,
    $invoiceHash,
    $uuid
);

if ($response->isSuccess()) {
    echo "PASS: " . $response->getValidationStatus();
}
```

Required types: Standard Invoice, Standard Credit Note, Standard Debit Note, Simplified Invoice, Simplified Credit Note, Simplified Debit Note.

> See [`examples/Certificates/ComplianceCheck.php`](examples/Certificates/ComplianceCheck.php) for an automated script that generates, signs, and submits all 6 types.

### Step 4: Request Production Certificate (PCSID)

> 📄 Included automatically in [`examples/Certificates/ComplianceCheck.php`](examples/Certificates/ComplianceCheck.php)

After all 6 invoices pass:

```php
$prodResult = $api->requestProductionCertificate(
    $creds['certificate'],
    $creds['secret'],
    $creds['requestId']
);

$api->saveToJson(
    $prodResult->getCertificate(),
    $prodResult->getSecret(),
    $prodResult->getRequestId(),
    'output/production_credentials.json'
);
```

## Submitting Invoices

### Reporting (B2C / Simplified)

> 📄 Full example: [`examples/InvoiceSimplified/simplified_invoice.php`](examples/InvoiceSimplified/simplified_invoice.php)

```php
$prod = json_decode(file_get_contents('output/production_credentials.json'), true);

$response = $api->submitReportingInvoice(
    $prod['certificate'], $prod['secret'],
    $signedXml, $invoiceHash, $uuid
);

$response->isReported();         // true if REPORTED
$response->getReportingStatus(); // "REPORTED"
$response->getValidationStatus();
$response->getWarningMessages();
$response->getErrorMessages();
```

### Clearance (B2B / Standard)

> 📄 Full example: [`examples/InvoiceStandard/standard_invoice.php`](examples/InvoiceStandard/standard_invoice.php)

```php
$response = $api->submitClearanceInvoice(
    $prod['certificate'], $prod['secret'],
    $signedXml, $invoiceHash, $uuid
);

$response->isCleared();                // true if CLEARED
$response->getClearedInvoice();        // Base64 stamped invoice
$response->getDecodedClearedInvoice(); // XML string
```

### Certificate Renewal

```php
$renewed = $api->renewProductionCertificate(
    $prod['certificate'], $prod['secret'],
    $newCsr, $otp
);
```

## Invoice Generation

### From Array (e-commerce integration)

```php
use Saleh7\Zatca\Mappers\InvoiceMapper;
use Saleh7\Zatca\GeneratorInvoice;

$invoiceData = [
    'uuid'            => '3cf5ee18-ee25-44ea-a444-2c37ba7f28be',
    'id'              => 'INV-001',
    'issueDate'       => '2025-01-15',
    'issueTime'       => '14:30:00',
    'currencyCode'    => 'SAR',
    'taxCurrencyCode' => 'SAR',
    'invoiceType'     => [
        'invoice' => 'simplified',  // or 'standard'
        'type'    => 'invoice',     // 'invoice', 'credit', or 'debit'
    ],
    'supplier' => [
        'registrationName' => 'Your Company',
        'taxId'            => '3XXXXXXXXXXXXX3',
        'identificationId' => 'XXXXXXXXXX',
        'identificationType' => 'CRN',
        'address' => [
            'street' => 'Main Street', 'buildingNumber' => '1234',
            'subdivision' => 'District', 'city' => 'Riyadh',
            'postalZone' => '12345', 'country' => 'SA',
        ],
    ],
    'invoiceLines' => [
        [
            'id' => 1, 'unitCode' => 'PCE', 'quantity' => 2,
            'lineExtensionAmount' => 200,
            'item' => [
                'name' => 'Product Name',
                'classifiedTaxCategory' => [['percent' => 15, 'taxScheme' => ['id' => 'VAT']]],
            ],
            'price' => ['amount' => 100, 'unitCode' => 'UNIT'],
            'taxTotal' => ['taxAmount' => 30, 'roundingAmount' => 230],
        ],
    ],
    // ... taxTotal, legalMonetaryTotal, etc.
];

$invoice = (new InvoiceMapper())->mapToInvoice($invoiceData);
$xml = GeneratorInvoice::invoice($invoice)->getXML();
```

### Signing

```php
use Saleh7\Zatca\Helpers\Certificate;
use Saleh7\Zatca\InvoiceSigner;

$certificate = new Certificate($certString, $privateKeyString, $secret);
$signer = InvoiceSigner::signInvoice($xml, $certificate);

$signedXml   = $signer->getInvoice();
$invoiceHash = $signer->getHash();
```

## API Reference

| Method | Endpoint | Returns |
|--------|----------|---------|
| `requestComplianceCertificate($csr, $otp)` | POST /compliance | `ComplianceCertificateResult` |
| `validateInvoiceCompliance(...)` | POST /compliance/invoices | `ComplianceInvoiceResponse` |
| `requestProductionCertificate(...)` | POST /production/csids | `ProductionCertificateResult` |
| `renewProductionCertificate(...)` | PATCH /production/csids | `ProductionCertificateResult` |
| `submitReportingInvoice(...)` | POST /invoices/reporting/single | `ReportingResponse` |
| `submitClearanceInvoice(...)` | POST /invoices/clearance/single | `ClearanceResponse` |

All response objects extend `ApiResponse` with: `isSuccess()`, `hasWarnings()`, `hasErrors()`, `getValidationStatus()`, `getWarningMessages()`, `getErrorMessages()`, `toArray()`.

## Examples

| File | Description |
|------|-------------|
| [`Certificates/GeneratorCertificate.php`](examples/Certificates/GeneratorCertificate.php) | Generate CSR + private key |
| [`Certificates/RequestComplianceCertificate.php`](examples/Certificates/RequestComplianceCertificate.php) | Request compliance certificate |
| [`Certificates/ComplianceCheck.php`](examples/Certificates/ComplianceCheck.php) | Automated 6-invoice compliance + production cert |
| [`InvoiceSimplified/simplified_invoice.php`](examples/InvoiceSimplified/simplified_invoice.php) | Simplified invoice generation + signing |
| [`InvoiceStandard/standard_invoice.php`](examples/InvoiceStandard/standard_invoice.php) | Standard invoice generation + signing |

## Contributing

Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change. Please make sure to update tests as appropriate.

## Contributors

<a href="https://github.com/sevaske"><img src="https://github.com/sevaske.png" width="60px;"/></a>
<a href="https://github.com/habibalkhabbaz"><img src="https://github.com/habibalkhabbaz.png" width="60px;"/></a>
## License

[MIT License](https://github.com/Saleh7/php-zatca-xml/blob/main/LICENSE)
