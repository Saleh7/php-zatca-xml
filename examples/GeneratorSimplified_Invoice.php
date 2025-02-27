<?php
require __DIR__ . '/../vendor/autoload.php';

use Saleh7\Zatca\{
    SignatureInformation,UBLDocumentSignatures,ExtensionContent,UBLExtension,UBLExtensions,Signature,InvoiceType,AdditionalDocumentReference,
    TaxScheme,PartyTaxScheme,Address,LegalEntity,Delivery,Party,PaymentMeans,TaxCategory,
    AllowanceCharge,TaxSubTotal,TaxTotal,LegalMonetaryTotal,ClassifiedTaxCategory,Item,Price,InvoiceLine,
    GeneratorInvoice,Invoice,UnitCode,OrderReference,BillingReference,Contract,Attachment
};

// --- Signature Information & UBL Document Signatures ---
$signatureInfo = (new SignatureInformation)
    ->setReferencedSignatureID("urn:oasis:names:specification:ubl:signature:Invoice")
    ->setID('urn:oasis:names:specification:ubl:signature:1');

$ublDocSignatures = (new UBLDocumentSignatures)
    ->setSignatureInformation($signatureInfo);

$extensionContent = (new ExtensionContent)
    ->setUBLDocumentSignatures($ublDocSignatures);

$ublExtension = (new UBLExtension)
    ->setExtensionURI('urn:oasis:names:specification:ubl:dsig:enveloped:xades')
    ->setExtensionContent($extensionContent);

// Default UBL Extensions Default
$ublExtensions = (new UBLExtensions)
    ->setUBLExtensions([$ublExtension]);

// --- Signature Default ---
$signature = (new Signature)
    ->setId("urn:oasis:names:specification:ubl:signature:Invoice")
    ->setSignatureMethod("urn:oasis:names:specification:ubl:dsig:enveloped:xades");

// --- Invoice Type ---
$invoiceType = (new InvoiceType())
    ->setInvoice('simplified') // 'standard' or 'simplified'
    ->setInvoiceType('invoice') // 'invoice', 'debit', or 'credit', 'prepayment'
    ->setIsThirdParty(false) // Third-party transaction
    ->setIsNominal(false) // Nominal transaction
    ->setIsExportInvoice(false) // Export invoice
    ->setIsSummary(false) // Summary invoice
    ->setIsSelfBilled(false); // Self-billed invoice

// add Attachment
$attachment = (new Attachment())
    ->setBase64Content('NWZlY2ViNjZmZmM4NmYzOGQ5NTI3ODZjNmQ2OTZjNzljMmRiYzIzOWRkNGU5MWI0NjcyOWQ3M2EyN2ZiNTdlOQ==',
    'base64', 
    'text/plain'
);

// --- Additional Document References ---
$additionalDocs = [];

// icv = Invoice counter value
$additionalDocs[] = (new AdditionalDocumentReference())
    ->setId('ICV')
    ->setUUID("10"); //Invoice counter value

// pih = Previous Invoice Hash
$additionalDocs[] = (new AdditionalDocumentReference())
    ->setId('PIH')
    ->setAttachment($attachment); // Previous Invoice Hash

// qr = QR Code Default
$additionalDocs[] = (new AdditionalDocumentReference())
    ->setId('QR');

// --- Tax Scheme & Party Tax Schemes ---
$taxScheme = (new TaxScheme())
    ->setId("VAT");

// --- Legal Entity Company ---
$legalEntityCompany = (new LegalEntity())
    ->setRegistrationName('Maximum Speed Tech Supply');

// --- Party Tax Scheme Company ---
$partyTaxSchemeCompany = (new PartyTaxScheme())
    ->setTaxScheme($taxScheme)
    ->setCompanyId('399999999900003');

// --- Address Company ---
$addressCompany = (new Address())
    ->setStreetName('Prince Sultan')
    ->setBuildingNumber("2322")
    ->setCitySubdivisionName('Al-Murabba')
    ->setCityName('Riyadh')
    ->setPostalZone('23333')
    ->setCountry('SA');

 // --- Supplier Company ---
$supplierCompany = (new Party())
->setPartyIdentification("1010010000")
->setPartyIdentificationId("CRN")
->setLegalEntity($legalEntityCompany)
->setPartyTaxScheme($partyTaxSchemeCompany)
->setPostalAddress($addressCompany);


// --- Legal Entity Customer ---
$legalEntityCustomer = (new LegalEntity())
    ->setRegistrationName('Fatoora Samples');

// --- Party Tax Scheme Customer ---
$partyTaxSchemeCustomer = (new PartyTaxScheme())
    ->setTaxScheme($taxScheme)
    ->setCompanyId('399999999800003');

// --- Address Customer ---
$addressCustomer = (new Address())
    ->setStreetName('Salah Al-Din')
    ->setBuildingNumber("1111")
    ->setCitySubdivisionName('Al-Murooj')
    ->setCityName('Riyadh')
    ->setPostalZone('12222')
    ->setCountry('SA');

// --- Supplier Customer ---
$supplierCustomer = (new Party())
    ->setLegalEntity($legalEntityCustomer)
    ->setPartyTaxScheme($partyTaxSchemeCustomer)
    ->setPostalAddress($addressCustomer);

// --- Payment Means ---
$paymentMeans = (new PaymentMeans())
    ->setPaymentMeansCode("10");


// --- array of Tax Category Discount ---
$taxCategoryDiscount = [];

// --- Tax Category Discount ---
$taxCategoryDiscount[] = (new TaxCategory())
    ->setPercent(15)
    ->setTaxScheme($taxScheme);

// --- Allowance Charge (for Invoice Line) ---
$allowanceCharges = [];
$allowanceCharges[] = (new AllowanceCharge())
    ->setChargeIndicator(false)
    ->setAllowanceChargeReason('discount')
    ->setAmount(0.00)
    ->setTaxCategory($taxCategoryDiscount);// Tax Category Discount

// --- Tax Category ---
$taxCategorySubTotal = (new TaxCategory())
    ->setPercent(15)
    ->setTaxScheme($taxScheme);

// --- Tax Sub Total ---
$taxSubTotal = (new TaxSubTotal())
    ->setTaxableAmount(4)
    ->setTaxAmount(0.6)
    ->setTaxCategory($taxCategorySubTotal);

// --- Tax Total ---
$taxTotal = (new TaxTotal())
    ->addTaxSubTotal($taxSubTotal)
    ->setTaxAmount(0.6);

// --- Legal Monetary Total ---
$legalMonetaryTotal = (new LegalMonetaryTotal())
    ->setLineExtensionAmount(4)// Total amount of the invoice
    ->setTaxExclusiveAmount(4) // Total amount without tax
    ->setTaxInclusiveAmount(4.60) // Total amount with tax
    ->setPrepaidAmount(0) // Prepaid amount
    ->setPayableAmount(4.60) // Amount to be paid
    ->setAllowanceTotalAmount(0); // Total amount of allowances

// --- Classified Tax Category ---
$classifiedTax = (new ClassifiedTaxCategory())
    ->setPercent(15)
    ->setTaxScheme($taxScheme);

// --- Item (Product) ---
$productItem = (new Item())
    ->setName('Product') // Product name
    ->setClassifiedTaxCategory($classifiedTax); // Classified tax category

// --- Allowance Charge (for Price) ---
$allowanceChargesPrice = [];
$allowanceChargesPrice[] = (new AllowanceCharge())
    ->setChargeIndicator(true)
    ->setAllowanceChargeReason('discount')
    ->setAmount(0.00);

// --- Price ---
$price = (new Price())
    ->setUnitCode(UnitCode::UNIT)
    ->setAllowanceCharges($allowanceChargesPrice)
    ->setPriceAmount(2);

// --- Invoice Line Tax Total ---
$lineTaxTotal = (new TaxTotal())
    ->setTaxAmount(0.60)
    ->setRoundingAmount(4.60);

// --- Invoice Line(s) ---
$invoiceLine = (new InvoiceLine())
    ->setUnitCode("PCE")
    ->setId(1)
    ->setItem($productItem)
    ->setLineExtensionAmount(4)
    ->setPrice($price)
    ->setTaxTotal($lineTaxTotal)
    ->setInvoicedQuantity(2);
$invoiceLines = [$invoiceLine];


// Invoice
$invoice = (new Invoice())
    ->setUBLExtensions($ublExtensions)
    ->setUUID('3cf5ee18-ee25-44ea-a444-2c37ba7f28be')
    ->setId('SME00023')
    ->setIssueDate(new DateTime('2024-09-07 17:41:08'))
    ->setIssueTime(new DateTime('2024-09-07 17:41:08'))
    ->setInvoiceType($invoiceType)
    ->setNote('ABC')->setlanguageID('ar')
    ->setInvoiceCurrencyCode('SAR') // Currency code (ISO 4217)
    ->setTaxCurrencyCode('SAR') // Tax currency code (ISO 4217)
    ->setAdditionalDocumentReferences($additionalDocs) // Additional document references
    ->setAccountingSupplierParty($supplierCompany)// Supplier company
    ->setAccountingCustomerParty($supplierCustomer) // Customer company
    ->setPaymentMeans($paymentMeans)// Payment means
    ->setAllowanceCharges($allowanceCharges)// Allowance charges
    ->setTaxTotal($taxTotal)// Tax total
    ->setLegalMonetaryTotal($legalMonetaryTotal)// Legal monetary total
    ->setInvoiceLines($invoiceLines)// Invoice lines
    ->setSignature($signature);


try {
    // Generate the XML (default currency 'SAR')
    // Save the XML to an output file
    GeneratorInvoice::invoice($invoice)->saveXMLFile('GeneratorSimplified_Invoice.xml');

} catch (\Exception $e) {
    // Log error message and exit
    echo "An error occurred: " . $e->getMessage() . "\n";
    exit(1);
}