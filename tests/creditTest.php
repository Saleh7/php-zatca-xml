<?php
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
    ->setInvoiceType('Credit'); // Invoice / Debit / Credit
// invoiceType object
$inType = (new \Saleh7\Zatca\BillingReference())
    ->setId('SME00002');

// invoiceType object
$Contact = (new \Saleh7\Zatca\Contract())
    ->setId('15');


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

$partyTaxScheme = (new \Saleh7\Zatca\PartyTaxScheme())
    ->setTaxScheme($taxScheme)
    ->setCompanyId('311111111101113');

$partyTaxSchemeCustomer = (new \Saleh7\Zatca\PartyTaxScheme())
    ->setTaxScheme($taxScheme);

$address = (new \Saleh7\Zatca\Address())
    ->setStreetName('الامير سلطان')
    ->setBuildingNumber(2322)
    ->setPlotIdentification(2223)
    ->setCitySubdivisionName('الرياض')
    ->setCityName('الرياض | Riyadh')
    ->setPostalZone('23333')
    ->setCountry('SA');

$legalEntity = (new \Saleh7\Zatca\LegalEntity())
        ->setRegistrationName('Acme Widget’s LTD');

$delivery = (new \Saleh7\Zatca\Delivery())
    ->setActualDeliveryDate("2022-09-07");

$supplierCompany = (new \Saleh7\Zatca\Party())
    ->setPartyIdentification("311111111111113")
    ->setPartyIdentificationId("CRN")
    ->setLegalEntity($legalEntity)
    ->setPartyTaxScheme($partyTaxScheme)
    ->setPostalAddress($address);

$supplierCustomer = (new \Saleh7\Zatca\Party())
    ->setPartyIdentification("311111111111113")
    ->setPartyIdentificationId("NAT")
    ->setLegalEntity($legalEntity)
    ->setPartyTaxScheme($partyTaxSchemeCustomer)
    ->setPostalAddress($address);

$clientPaymentMeans = (new \Saleh7\Zatca\PaymentMeans())
    ->setInstructionNote("CANCELLATION_OR_TERMINATION")
    ->setPaymentMeansCode("10");


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

$lineTaxTotalOne = (new \Saleh7\Zatca\TaxTotal())
    ->setTaxAmount(0.6);

$taxSubTotal = (new \Saleh7\Zatca\TaxSubTotal())
    ->setTaxableAmount(4)
    ->setTaxAmount(0.6)
    ->setTaxCategory($taxCategory);

$taxTotal = (new \Saleh7\Zatca\TaxTotal())
    ->addTaxSubTotal($taxSubTotal)
    ->setTaxAmount(0.6);

$legalMonetaryTotal = (new \Saleh7\Zatca\LegalMonetaryTotal())
    ->setLineExtensionAmount(4)
    ->setTaxExclusiveAmount(4)
    ->setTaxInclusiveAmount(4.60)
    ->setPrepaidAmount(0)
    ->setPayableAmount(4.60)
    ->setAllowanceTotalAmount(0);


$classifiedTax = (new \Saleh7\Zatca\ClassifiedTaxCategory())
    ->setPercent(15)
    ->setTaxScheme($taxScheme);

// Product
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
// Invoice object
$invoice = (new \Saleh7\Zatca\Invoice())
    ->setUBLExtensions($UBLExtensions)
    ->setUUID('3cf5ee18-ee25-44ea-a444-2c37ba7f28be')
    ->setId('SME00023')
    ->setIssueDate(new \DateTime())
    ->setIssueTime(new \DateTime())
    ->setInvoiceType($invoiceType)
    ->Signature($Signature)
    // ->setContract($Contact)
    ->setBillingReference($inType)
    ->setAdditionalDocumentReferences($AdditionalDocumentReferences)
    ->setDelivery($delivery)
    ->setAllowanceCharges($allowanceCharges)
    ->setPaymentMeans($clientPaymentMeans)
    ->setTaxTotal($taxTotal)
    ->setInvoiceLines($invoiceLines)
    ->setLegalMonetaryTotal($legalMonetaryTotal)
    ->setAccountingCustomerParty($supplierCustomer)
    ->setAccountingSupplierParty($supplierCompany);

$generatorXml = new \Saleh7\Zatca\GeneratorInvoice();
$outputXML = $generatorXml->invoice($invoice);
header("Content-Type: application/xml; charset=utf-8");
echo $outputXML;
?>
