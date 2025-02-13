<?php
require __DIR__ . '/vendor/autoload.php';

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

$ublExtensions = (new UBLExtensions)
    ->setUBLExtensions([$ublExtension]);

// --- Signature ---
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
$additionalDocs[] = (new AdditionalDocumentReference())
    ->setId('ICV')
    ->setUUID("23"); //Invoice counter value
$additionalDocs[] = (new AdditionalDocumentReference())
    ->setId('PIH')
    ->setAttachment($attachment); // Previous Invoice Hash
    // ->setPreviousInvoiceHash('NWZlY2ViNjZmZmM4NmYzOGQ5NTI3ODZjNmQ2OTZjNzljMmRiYzIzOWRkNGU5MWI0NjcyOWQ3M2EyN2ZiNTdlOQ=='); // Previous Invoice Hash
$additionalDocs[] = (new AdditionalDocumentReference())
    ->setId('QR');

// --- Tax Scheme & Party Tax Schemes ---
$taxScheme = (new TaxScheme())
    ->setId("VAT");

$partyTaxSchemeSupplier = (new PartyTaxScheme())
    ->setTaxScheme($taxScheme)
    ->setCompanyId('311111111101113');

$partyTaxSchemeCustomer = (new PartyTaxScheme())
    ->setTaxScheme($taxScheme);

// --- Address ---
$address = (new Address())
    ->setStreetName('الامير سلطان')
    ->setBuildingNumber("2322")
    ->setPlotIdentification("2223")
    ->setCitySubdivisionName('الرياض')
    ->setCityName('الرياض | Riyadh')
    ->setPostalZone('23333')
    ->setCountry('SA');

// --- Legal Entity ---
$legalEntity = (new LegalEntity())
    ->setRegistrationName('Acme Widget’s LTD');

// --- Delivery ---
$delivery = (new Delivery())
    ->setActualDeliveryDate("2022-09-07");
    // ->setLatestDeliveryDate("2022-09-07"); // If the invoice contains a supply end date (KSA-24), then the invoice must contain a supply date (KSA-5).

// --- Parties ---
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

// --- Payment Means ---
$paymentMeans = (new PaymentMeans())
    ->setPaymentMeansCode(\Saleh7\Zatca\Enums\PaymentTypeEnum::Cash->value);

// --- Tax Category ---
$taxCategory = (new TaxCategory())
    ->setPercent(15)
    ->setTaxScheme($taxScheme);

// --- Allowance Charge (for Invoice Line) ---
$allowanceCharges = [];
$allowanceCharges[] = (new AllowanceCharge())
    ->setChargeIndicator(false)
    ->setAllowanceChargeReason('discount')
    ->setAmount(0.00)
    ->setTaxCategory($taxCategory);

// --- Tax Sub Total & Tax Total ---
$taxSubTotal = (new TaxSubTotal())
    ->setTaxableAmount(4)
    ->setTaxAmount(0.6)
    ->setTaxCategory($taxCategory);

$taxTotal = (new TaxTotal())
    ->addTaxSubTotal($taxSubTotal)
    ->setTaxAmount(0.6);

// --- Legal Monetary Total ---
$legalMonetaryTotal = (new LegalMonetaryTotal())
    ->setLineExtensionAmount(4)
    ->setTaxExclusiveAmount(4)
    ->setTaxInclusiveAmount(4.60)
    ->setPrepaidAmount(0)
    ->setPayableAmount(4.60)
    ->setAllowanceTotalAmount(0);

// --- Classified Tax Category ---
$classifiedTax = (new ClassifiedTaxCategory())
    ->setPercent(15)
    ->setTaxScheme($taxScheme);


// --- Item (Product) ---
$productItem = (new Item())
    ->setName('قلم رصاص')
    ->setClassifiedTaxCategory($classifiedTax);

// --- Allowance Charge (for Price) ---
$allowanceChargesPrice = [];
$allowanceChargesPrice[] = (new AllowanceCharge())
    ->setChargeIndicator(false)
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

// --- Order Reference ---
$orderReference = (new OrderReference())
    ->setId('SME00023') // Purchase order ID
    ->setSalesOrderId('SME00023');

// --- Billing Reference ---
$billingReferences = [
    (new BillingReference())->setId('SME00023') // Billing reference ID
];

// add Contract
$contract = (new Contract())
    ->setId('SME00023');
// Invoice
$invoice = (new Invoice())
    // ->setUBLExtensions($ublExtensions)
    ->setUUID('3cf5ee18-ee25-44ea-a444-2c37ba7f28be')
    ->setId('SME00023')
    ->setIssueDate(new DateTime())
    ->setIssueTime(new DateTime())
    ->setInvoiceType($invoiceType)
    // ->setNote('sss')->setlanguageID('ar')
    ->setInvoiceCurrencyCode('SAR') // Currency code (ISO 4217)
    ->setTaxCurrencyCode('SAR') // Tax currency code (ISO 4217)
    // ->setOrderReference($orderReference) // Order reference
    // ->setBillingReferences($billingReferences) // Order reference
    // ->setContract($contract) // Contract ID	The identification of a contract.
    ->setAdditionalDocumentReferences($additionalDocs) // Additional document references
    ->setAccountingSupplierParty($supplierCompany)// Supplier company
    ->setAccountingCustomerParty($supplierCustomer) // Customer company
    // ->setDelivery($delivery)// Delivery
    // ->setPaymentMeans($paymentMeans)// Payment means
    ->setAllowanceCharges($allowanceCharges)// Allowance charges
    ->setTaxTotal($taxTotal)// Tax total
    ->setLegalMonetaryTotal($legalMonetaryTotal)// Legal monetary total
    ->setInvoiceLines($invoiceLines)// Invoice lines
    ->setSignature($signature);// Signature

// Generator Invoice
$generatorXml = new GeneratorInvoice();
$outputXML = $generatorXml->invoice($invoice);
// Load the XML into a DOMDocument
$dom = new DOMDocument('1.0', 'UTF-8');
$dom->preserveWhiteSpace = false;
$dom->formatOutput = true;
$dom->loadXML($outputXML);
$dom->encoding = 'UTF-8';
$formattedXml = $dom->saveXML();

// Convert 2-space indentation to 4-space indentation
$formattedXml = preg_replace_callback('/^([ ]+)/m', function($matches) {
    return str_repeat('    ', strlen($matches[1]) / 2);
}, $formattedXml);

echo $formattedXml;