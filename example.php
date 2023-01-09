<?php
require __DIR__ . '/vendor/autoload.php';

// SignatureInformation
$sign = (new \Saleh7\Zatca\SignatureInformation)
    ->setReferencedSignatureID("urn:oasis:names:specification:ubl:signature:Invoice")
    ->setID('urn:oasis:names:specification:ubl:signature:1');

// UBLDocumentSignatures
$ublDecoment = (new \Saleh7\Zatca\UBLDocumentSignatures)
    ->setSignatureInformation($sign);

$extensionContent = (new \Saleh7\Zatca\ExtensionContent)
    ->setUBLDocumentSignatures($ublDecoment);

// UBLExtension
$UBLExtension[] = (new \Saleh7\Zatca\UBLExtension)
    ->setExtensionURI('urn:oasis:names:specification:ubl:dsig:enveloped:xades')
    ->setExtensionContent($extensionContent);

$UBLExtensions = (new \Saleh7\Zatca\UBLExtensions)
    ->setUBLExtensions($UBLExtension);

$Signature = (new \Saleh7\Zatca\Signature)
    ->setId("urn:oasis:names:specification:ubl:signature:Invoice")
    ->setSignatureMethod("urn:oasis:names:specification:ubl:dsig:enveloped:xades");
// invoiceType object
$invoiceType = (new \Saleh7\Zatca\InvoiceType())
    ->setInvoice('Invoice') // Invoice / Simplified
    ->setInvoiceType('Invoice'); // Invoice / Debit / Credit

// AdditionalDocumentReference
$AdditionalDocumentReferences = [];
$AdditionalDocumentReferences[] = (new \Saleh7\Zatca\AdditionalDocumentReference())
    ->setId('ICV')
    ->setUUID(23);

$AdditionalDocumentReferences[] = (new \Saleh7\Zatca\AdditionalDocumentReference())
    ->setId('PIH')
    ->setPreviousInvoiceHash('NWZlY2ViNjZmZmM4NmYzOGQ5NTI3ODZjNmQ2OTZjNzljMmRiYzIzOWRkNGU5MWI0NjcyOWQ3M2EyN2ZiNTdlOQ==');

$AdditionalDocumentReferences[] = (new \Saleh7\Zatca\AdditionalDocumentReference())
    ->setId('QR');

// Tax scheme
$taxScheme = (new \Saleh7\Zatca\TaxScheme())
    ->setId("VAT");

// Party Tax Scheme
$partyTaxScheme = (new \Saleh7\Zatca\PartyTaxScheme())
    ->setTaxScheme($taxScheme)
    ->setCompanyId('311111111101113');

// party Tax Scheme Customer
$partyTaxSchemeCustomer = (new \Saleh7\Zatca\PartyTaxScheme())
    ->setTaxScheme($taxScheme);

// Address
$address = (new \Saleh7\Zatca\Address())
    ->setStreetName('الامير سلطان')
    ->setBuildingNumber(2322)
    ->setPlotIdentification(2223)
    ->setCitySubdivisionName('الرياض')
    ->setCityName('الرياض | Riyadh')
    ->setPostalZone('23333')
    ->setCountry('SA');

// Legal Entity
$legalEntity = (new \Saleh7\Zatca\LegalEntity())
        ->setRegistrationName('Acme Widget’s LTD');

// Delivery
$delivery = (new \Saleh7\Zatca\Delivery())
    ->setActualDeliveryDate("2022-09-07");

// Party supplier Company
$supplierCompany = (new \Saleh7\Zatca\Party())
    ->setPartyIdentification("311111111111113")
    ->setPartyIdentificationId("CRN")
    ->setLegalEntity($legalEntity)
    ->setPartyTaxScheme($partyTaxScheme)
    ->setPostalAddress($address);

// Party supplier Customer
$supplierCustomer = (new \Saleh7\Zatca\Party())
    ->setPartyIdentification("311111111111113")
    ->setPartyIdentificationId("NAT")
    ->setLegalEntity($legalEntity)
    ->setPartyTaxScheme($partyTaxSchemeCustomer)
    ->setPostalAddress($address);

// Payment Means
$clientPaymentMeans = (new \Saleh7\Zatca\PaymentMeans())
    ->setPaymentMeansCode("10");

// Tax Category
$taxCategory = (new \Saleh7\Zatca\TaxCategory())
    ->setPercent(15)
    ->setTaxScheme($taxScheme);

// Invoice Line(s)
$allowanceCharges = [];
$allowanceCharges[] = (new \Saleh7\Zatca\AllowanceCharge())
->setChargeIndicator(false)
->setAllowanceChargeReason('discount')
->setAmount(0.00)
->setTaxCategory($taxCategory);

// Tax Sub Total
$taxSubTotal = (new \Saleh7\Zatca\TaxSubTotal())
    ->setTaxableAmount(4)
    ->setTaxAmount(0.6)
    ->setTaxCategory($taxCategory);

// Tax Total
$taxTotal = (new \Saleh7\Zatca\TaxTotal())
    ->addTaxSubTotal($taxSubTotal)
    ->setTaxAmount(0.6);

// Legal Monetary Total
$legalMonetaryTotal = (new \Saleh7\Zatca\LegalMonetaryTotal())
    ->setLineExtensionAmount(4)
    ->setTaxExclusiveAmount(4)
    ->setTaxInclusiveAmount(4.60)
    ->setPrepaidAmount(0)
    ->setPayableAmount(4.60)
    ->setAllowanceTotalAmount(0);

// Classified Tax Category
$classifiedTax = (new \Saleh7\Zatca\ClassifiedTaxCategory())
    ->setPercent(15)
    ->setTaxScheme($taxScheme);

// Item Product
$productItem = (new \Saleh7\Zatca\Item())
    ->setName('قلم رصاص')
    ->setClassifiedTaxCategory($classifiedTax);

// Price
$price = (new \Saleh7\Zatca\Price())
    ->setUnitCode(\Saleh7\Zatca\UnitCode::UNIT)
    ->setPriceAmount(2);

// Invoice Line tax totals
$lineTaxTotal = (new \Saleh7\Zatca\TaxTotal())
    ->setTaxAmount(0.60)
    ->setRoundingAmount(4.60);

// Invoice Line(s)
$invoiceLines = [];
$invoiceLines[] = (new \Saleh7\Zatca\InvoiceLine())
    ->setUnitCode("PCE")
    ->setId(1)
    ->setItem($productItem)
    ->setLineExtensionAmount(4)
    ->setPrice($price)
    ->setTaxTotal($lineTaxTotal)
    ->setInvoicedQuantity(2);

// Invoice
$invoice = (new \Saleh7\Zatca\Invoice())
    ->setUBLExtensions($UBLExtensions)
    ->setUUID('3cf5ee18-ee25-44ea-a444-2c37ba7f28be')
    ->setId('SME00023')
    ->setIssueDate(new \DateTime())
    ->setIssueTime(new \DateTime())
    ->setInvoiceType($invoiceType)
    ->Signature($Signature)
    ->setAdditionalDocumentReferences($AdditionalDocumentReferences)
    ->setDelivery($delivery)
    ->setAllowanceCharges($allowanceCharges)
    ->setPaymentMeans($clientPaymentMeans)
    ->setTaxTotal($taxTotal)
    ->setInvoiceLines($invoiceLines)
    ->setLegalMonetaryTotal($legalMonetaryTotal)
    ->setAccountingCustomerParty($supplierCustomer)
    ->setAccountingSupplierParty($supplierCompany);

// Generator Invoice
$generatorXml = new \Saleh7\Zatca\GeneratorInvoice();
$outputXML = $generatorXml->invoice($invoice);
header("Content-Type: application/xml; charset=utf-8");
echo $outputXML;
