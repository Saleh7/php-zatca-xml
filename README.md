<p align="center">
    <img src="https://i.imgur.com/hLSMzHU.png"  alt="php-zatca-xml">
</p>


<p align="center">
<img src="https://badgen.net/packagist/php/saleh7/php-zatca-xml" alt="php Version">
<a href="https://packagist.org/packages/saleh7/php-zatca-xml"><img alt="Packagist Stars" src="https://img.shields.io/packagist/stars/saleh7/php-zatca-xml"></a>
<a href="https://packagist.org/packages/saleh7/php-zatca-xml"><img alt="Packagist Downloads" src="https://img.shields.io/packagist/dt/saleh7/php-zatca-xml"></a>
<a href="https://packagist.org/packages/saleh7/php-zatca-xml"><img alt="Packagist Version" src="https://img.shields.io/packagist/v/saleh7/php-zatca-xml"></a>
<a href="https://packagist.org/packages/saleh7/php-zatca-xml"><img alt="License" src="https://img.shields.io/badge/License-MIT-yellow.svg"></a>
</p>


<p align="center">
Please feel free to <a href="https://github.com/Saleh7/php-zatca-xml/pulls?q=sort%3Aupdated-desc+is%3Apr+is%3Aopen"><strong>contribute</strong></a> if you are missing features or tags
<br />
<a href="https://github.com/Saleh7/php-zatca-xml/tree/main/examples">View Examples</a>
·
<a href="https://github.com/Saleh7/php-zatca-xml/issues">Report a bug</a>
·
<a href="https://github.com/Saleh7/php-zatca-xml/issues">Request a feature</a>
·
<a href="https://github.com/Saleh7/php-zatca-xml/discussions">Ask questions</a>
</p>

## 📖 Introduction  

**PHP-ZATCA-XML** is an unofficial PHP library for generating **ZATCA Fatoora** e-invoices. It simplifies the process of creating compliant e-invoices, generating QR codes, handling certificates, and submitting invoices to **ZATCA’s servers**.  

Designed for **easy integration**, this library provides developers with a **customizable, robust, and efficient toolkit** to automate the ZATCA e-invoicing process in PHP applications.

## 🚀 Planned Features  

We are actively working on expanding the capabilities of this library. If you're a developer and would like to contribute, your help is highly appreciated! 💡  

- [ ] **XML to JSON Conversion** – Support for converting invoices from XML to JSON format.  
- [ ] **JSON/Array to Invoice** – Ability to generate invoices directly from JSON or array structures.  
- [ ] **Simplified Invoice Creation** – Streamlined generation of **Simplified Invoices**, **Debit**, and **Credit** compliant with ZATCA simplified.  
- [ ] **Standard Invoice Creation** – Streamlined generation **Standard Invoices**, **Debit**, and **Credit** compliant with ZATCA standards.  
- [ ] **Invoice to PDF Conversion** – Generate PDF versions of invoices for easy sharing and record-keeping.  

💡 **Got an idea?** Feel free to suggest it or contribute!  
 Let's build something great together! 🚀  

## ✨ Features  

- 🚀 **ZATCA-Compliant** – Easily generate valid e-invoices for ZATCA regulations  
- 📜 **Invoice Creation** – Generate standard and simplified invoices in XML format  
- 🔐 **Digital Signing** – Sign invoices securely to ensure compliance  
- 🏷 **QR Code Generation** – Automatically generate QR codes for invoices  
- 📡 **Direct Submission to ZATCA** – Send invoices directly to ZATCA’s servers  
- ⚡ **Lightweight & Fast** – Optimized for performance and easy integration in PHP projects  
- 🔄 **Customizable & Extensible** – Easily adapt the library to your needs  


## 📌 Requirements  

### ✅ PHP Version  
- **PHP 8.1 or higher**

### ✅ Required PHP Extensions  
Ensure the following PHP extensions are installed and enabled:  
- **`ext-dom`**
- **`ext-libxml`**
- **`ext-openssl`**
- **`ext-hash`**
- **`ext-mbstring`**


## 🛠 Installation  

```bash
composer require saleh7/php-zatca-xml
```

## 🚀 Usage  

This library simplifies the process of generating **ZATCA-compliant** e-invoices, handling **certificates**, signing invoices, and submitting them to **ZATCA’s API**. Below are the main usage examples:

---

### 📜 **1. Generating a Compliance Certificate**  

First, generate a **certificate signing request (CSR)** and private key:  

```php
use Saleh7\Zatca\CertificateBuilder;
use Saleh7\Zatca\CertificateBuilderException;

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
```

### 🔐 **2. Requesting a Compliance Certificate from ZATCA**  

Once the CSR is generated, you need to request a **compliance certificate** from **ZATCA's API**.  

```php
use Saleh7\Zatca\ZatcaAPI;
use Saleh7\Zatca\Exceptions\ZatcaApiException;

$zatcaClient = new ZatcaAPI('sandbox');

try {
    $otp = "123123"; // The OTP received from ZATCA
    $certificatePath = __DIR__ . '/output/certificate.csr';
    
    // Load the generated CSR
    $csr = $zatcaClient->loadCSRFromFile($certificatePath);
    
    // Request the compliance certificate from ZATCA
    $complianceResult = $zatcaClient->requestComplianceCertificate($csr, $otp);
    
    // Display the returned certificate and API secret
    echo "Compliance Certificate:\n" . $complianceResult->getCertificate() . "\n";
    echo "API Secret: " . $complianceResult->getSecret() . "\n";
    echo "Request ID: " . $complianceResult->getRequestId() . "\n";

    // Save the certificate details to a JSON file
    $outputFile = __DIR__ . '/output/ZATCA_certificate_data.json';
    $zatcaClient->saveToJson(
        $complianceResult->getCertificate(),
        $complianceResult->getSecret(),
        $complianceResult->getRequestId(),
        $outputFile
    );
    
    echo "Certificate data saved to {$outputFile}\n";

} catch (ZatcaApiException $e) {
    echo "API Error: " . $e->getMessage();
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
```

### 🧾 **3. Generating an Invoice XML**  

Now that we have the compliance certificate, we can generate a **ZATCA-compliant e-invoice in XML format**.

```php
use Saleh7\Zatca\{
    SignatureInformation, UBLDocumentSignatures, ExtensionContent, UBLExtension, UBLExtensions, Signature, 
    InvoiceType, AdditionalDocumentReference, TaxScheme, PartyTaxScheme, Address, LegalEntity, Delivery, 
    Party, PaymentMeans, TaxCategory, AllowanceCharge, TaxSubTotal, TaxTotal, LegalMonetaryTotal, 
    ClassifiedTaxCategory, Item, Price, InvoiceLine, GeneratorInvoice, Invoice, UnitCode, 
    OrderReference, BillingReference, Contract, Attachment
};

// --- Invoice Type ---
$invoiceType = (new InvoiceType())
    ->setInvoice('standard') // 'standard' or 'simplified'
    ->setInvoiceType('invoice') // 'invoice', 'debit', or 'credit', 'prepayment'
    ->setIsThirdParty(false) // Third-party transaction
    ->setIsNominal(false) // Nominal transaction
    ->setIsExportInvoice(false) // Export invoice
    ->setIsSummary(false) // Summary invoice
    ->setIsSelfBilled(false); // Self-billed invoice

// --- Supplier & Customer Information ---
$taxScheme = (new TaxScheme())->setId("VAT");

$partyTaxSchemeSupplier = (new PartyTaxScheme())->setTaxScheme($taxScheme)->setCompanyId('311111111101113');
$partyTaxSchemeCustomer = (new PartyTaxScheme())->setTaxScheme($taxScheme);

$address = (new Address())
    ->setStreetName('Prince Sultan Street')
    ->setBuildingNumber("2322")
    ->setPlotIdentification("2223")
    ->setCitySubdivisionName('Riyadh')
    ->setCityName('Riyadh')
    ->setPostalZone('23333')
    ->setCountry('SA');

$legalEntity = (new LegalEntity())->setRegistrationName('Acme Widget’s LTD');

$supplierCompany = (new Party())
    ->setPartyIdentification("311111111111113")
    ->setPartyIdentificationId("CRN")
    ->setLegalEntity($legalEntity)
    ->setPartyTaxScheme($partyTaxSchemeSupplier)
    ->setPostalAddress($address);

$supplierCustomer = (new Party())
    ->setPartyIdentification("311111111111113")
    ->setPartyIdentificationId("NAT")
    ->setLegalEntity($legalEntity)
    ->setPartyTaxScheme($partyTaxSchemeCustomer)
    ->setPostalAddress($address);

// --- Invoice Items & Pricing ---
$classifiedTax = (new ClassifiedTaxCategory())->setPercent(15)->setTaxScheme($taxScheme);
$productItem = (new Item())->setName('Pencil')->setClassifiedTaxCategory($classifiedTax);
$price = (new Price())->setUnitCode(UnitCode::UNIT)->setPriceAmount(2);

$lineTaxTotal = (new TaxTotal())->setTaxAmount(0.60)->setRoundingAmount(4.60);

$invoiceLine = (new InvoiceLine())
    ->setUnitCode("PCE")
    ->setId(1)
    ->setItem($productItem)
    ->setLineExtensionAmount(4)
    ->setPrice($price)
    ->setTaxTotal($lineTaxTotal)
    ->setInvoicedQuantity(2);

$invoiceLines = [$invoiceLine];

// --- Tax Totals ---
$taxSubTotal = (new TaxSubTotal())->setTaxableAmount(4)->setTaxAmount(0.6)->setTaxCategory($classifiedTax);
$taxTotal = (new TaxTotal())->addTaxSubTotal($taxSubTotal)->setTaxAmount(0.6);

// --- Legal Monetary Total ---
$legalMonetaryTotal = (new LegalMonetaryTotal())
    ->setLineExtensionAmount(4)
    ->setTaxExclusiveAmount(4)
    ->setTaxInclusiveAmount(4.60)
    ->setPrepaidAmount(0)
    ->setPayableAmount(4.60)
    ->setAllowanceTotalAmount(0);

// --- Build the Invoice ---
$invoice = (new Invoice())
    ->setUUID('3cf5ee18-ee25-44ea-a444-2c37ba7f28be')
    ->setId('SME00023')
    ->setIssueDate(new DateTime())
    ->setIssueTime(new DateTime())
    ->setInvoiceType($invoiceType)
    ->setInvoiceCurrencyCode('SAR')
    ->setTaxCurrencyCode('SAR')
    ->setAccountingSupplierParty($supplierCompany)
    ->setAccountingCustomerParty($supplierCustomer)
    ->setTaxTotal($taxTotal)
    ->setLegalMonetaryTotal($legalMonetaryTotal)
    ->setInvoiceLines($invoiceLines);
    // ......
// --- Generate XML ---
try {
    $generatorXml = new GeneratorInvoice();
    $outputXML = $generatorXml->invoice($invoice);
    
    // Save the XML to a file
    $filePath = __DIR__ . '/output/unsigned_invoice.xml';
    file_put_contents($filePath, $outputXML);
    
    echo "Invoice XML saved to: " . $filePath . "\n";

} catch (\Exception $e) {
    echo "An error occurred: " . $e->getMessage() . "\n";
    exit(1);
}
```

### ✍️ **4. Signing the Invoice XML**  

Before submitting the invoice to **ZATCA**, we need to **digitally sign** it using the **compliance certificate** obtained earlier.

```php
use Saleh7\Zatca\Helpers\Certificate;
use Saleh7\Zatca\InvoiceSigner;

// Load the unsigned invoice XML
$xmlInvoice = file_get_contents(__DIR__ . '/output/unsigned_invoice.xml');

// Load the compliance certificate data from the JSON file
$json_certificate = file_get_contents(__DIR__ . '/output/ZATCA_certificate_data.json');

// Decode the JSON data
$json_data = json_decode($json_certificate, true, 512, JSON_THROW_ON_ERROR);

// Extract certificate details
$certificate = $json_data[0]['certificate'];
$secret = $json_data[0]['secret'];

// Load the private key
$privateKey = file_get_contents(__DIR__ . '/output/private.pem');
$cleanPrivateKey = trim(str_replace(["-----BEGIN PRIVATE KEY-----", "-----END PRIVATE KEY-----"], "", $privateKey));

// Create a Certificate instance
$certificate = new Certificate(
    $certificate,
    $cleanPrivateKey,
    $secret
);

// Sign the invoice
$signedInvoice = InvoiceSigner::signInvoice($xmlInvoice, $certificate);

// Save the signed invoice
InvoiceSigner::signInvoice($xmlInvoice, $certificate)->saveXMLFile('/output/signed_invoice.xml');
```

### 📤 **5. Submitting the Signed Invoice to ZATCA**  

Once the invoice is **digitally signed**, it can be submitted to **ZATCA’s API** for compliance validation and clearance.  


## Contributing

Pull requests are welcome. For major changes, please open an issue first
to discuss what you would like to change.

Please make sure to update tests as appropriate.

## 👨‍💻 Contributors

<img src="https://github.com/sevaske.png" width="60px;"/><br /><sub><a href="https://github.com/sevaske">sevaske</a></sub>

Thank you all for your continuous support and contributions!

### Special Credits

This project has also benefited from some code snippets and ideas from the [SallaApp/ZATCA](https://github.com/SallaApp/ZATCA) repository. We appreciate their contribution to the community.

## License

This project is licensed under the [MIT License](https://github.com/Saleh7/php-zatca-xml/blob/main/LICENSE).
