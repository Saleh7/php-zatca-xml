<?php

use PHPUnit\Framework\TestCase;
use Saleh7\Zatca\{
    SignatureInformation,UBLDocumentSignatures,ExtensionContent,UBLExtension,UBLExtensions,Signature,InvoiceType,AdditionalDocumentReference,
    TaxScheme,PartyTaxScheme,Address,LegalEntity,Delivery,Party,PaymentMeans,TaxCategory,
    AllowanceCharge,TaxSubTotal,TaxTotal,LegalMonetaryTotal,ClassifiedTaxCategory,Item,Price,InvoiceLine,
    GeneratorInvoice,Invoice,UnitCode,OrderReference,BillingReference,Contract,Attachment
};

final class InvoiceTest extends TestCase
{
    /**
     * Test that invoice XML is generated correctly.
     */
    public function testInvoiceXmlGeneration(): void
    {
        // Create signature information
        $signatureInfo = (new SignatureInformation())
            ->setReferencedSignatureID("urn:oasis:names:specification:ubl:signature:Invoice")
            ->setID('urn:oasis:names:specification:ubl:signature:1');

        // Create UBL document signatures
        $ublDocSignatures = (new UBLDocumentSignatures())
            ->setSignatureInformation($signatureInfo);

        // Create extension content
        $extensionContent = (new ExtensionContent())
            ->setUBLDocumentSignatures($ublDocSignatures);

        // Create UBL extension
        $ublExtension = (new UBLExtension())
            ->setExtensionURI('urn:oasis:names:specification:ubl:dsig:enveloped:xades')
            ->setExtensionContent($extensionContent);

        // Create UBL extensions container
        $ublExtensions = (new UBLExtensions())
            ->setUBLExtensions([$ublExtension]);

        // Create signature
        $signature = (new Signature())
            ->setId("urn:oasis:names:specification:ubl:signature:Invoice")
            ->setSignatureMethod("urn:oasis:names:specification:ubl:dsig:enveloped:xades");

        // Configure invoice type
        $invoiceType = (new InvoiceType())
            ->setInvoice('simplified')
            ->setInvoiceType('invoice')
            ->setIsThirdParty(false)
            ->setIsNominal(false)
            ->setIsExportInvoice(false)
            ->setIsSummary(false)
            ->setIsSelfBilled(false);

        // Create attachment (for additional document reference)
        $attachment = (new Attachment())
            ->setBase64Content(
                'NWZlY2ViNjZmZmM4NmYzOGQ5NTI3ODZjNmQ2OTZjNzljMmRiYzIzOWRkNGU5MWI0NjcyOWQ3M2EyN2ZiNTdlOQ==',
                'base64',
                'text/plain'
            );

        // Create additional document references
        $additionalDocs = [];
        $additionalDocs[] = (new AdditionalDocumentReference())
            ->setId('ICV')
            ->setUUID("23");
        $additionalDocs[] = (new AdditionalDocumentReference())
            ->setId('PIH')
            ->setAttachment($attachment);
        $additionalDocs[] = (new AdditionalDocumentReference())
            ->setId('QR');

        // Create tax scheme and party tax schemes
        $taxScheme = (new TaxScheme())->setId("VAT");
        $partyTaxSchemeSupplier = (new PartyTaxScheme())
            ->setTaxScheme($taxScheme)
            ->setCompanyId('311111111101113');
        $partyTaxSchemeCustomer = (new PartyTaxScheme())
            ->setTaxScheme($taxScheme);

        // Create address
        $address = (new Address())
            ->setStreetName('الامير سلطان')
            ->setBuildingNumber("2322")
            ->setPlotIdentification("2223")
            ->setCitySubdivisionName('الرياض')
            ->setCityName('الرياض | Riyadh')
            ->setPostalZone('23333')
            ->setCountry('SA');

        // Create legal entity
        $legalEntity = (new LegalEntity())
            ->setRegistrationName('Acme Widget’s LTD');

        // Create supplier party
        $supplierCompany = (new Party())
            ->setPartyIdentification("311111111111113")
            ->setPartyIdentificationId("CRN")
            ->setLegalEntity($legalEntity)
            ->setPartyTaxScheme($partyTaxSchemeSupplier)
            ->setPostalAddress($address);

        // Create customer party
        $supplierCustomer = (new Party())
            ->setPartyIdentification("311111111111113")
            ->setPartyIdentificationId("NAT")
            ->setLegalEntity($legalEntity)
            ->setPartyTaxScheme($partyTaxSchemeCustomer)
            ->setPostalAddress($address);

        // Create tax category and allowance charge for invoice
        $taxCategory = (new TaxCategory())
            ->setPercent(15)
            ->setTaxScheme($taxScheme);
        $allowanceCharges = [];
        $allowanceCharges[] = (new AllowanceCharge())
            ->setChargeIndicator(false)
            ->setAllowanceChargeReason('discount')
            ->setAmount(0.00)
            ->setTaxCategory($taxCategory);

        // Create tax sub total and tax total
        $taxSubTotal = (new TaxSubTotal())
            ->setTaxableAmount(4)
            ->setTaxAmount(0.6)
            ->setTaxCategory($taxCategory);
        $taxTotal = (new TaxTotal())
            ->addTaxSubTotal($taxSubTotal)
            ->setTaxAmount(0.6);

        // Create legal monetary total
        $legalMonetaryTotal = (new LegalMonetaryTotal())
            ->setLineExtensionAmount(4)
            ->setTaxExclusiveAmount(4)
            ->setTaxInclusiveAmount(4.60)
            ->setPrepaidAmount(0)
            ->setPayableAmount(4.60)
            ->setAllowanceTotalAmount(0);

        // Create classified tax category and product item
        $classifiedTax = (new ClassifiedTaxCategory())
            ->setPercent(15)
            ->setTaxScheme($taxScheme);
        $productItem = (new Item())
            ->setName('قلم رصاص')
            ->setClassifiedTaxCategory($classifiedTax);

        // Create allowance charge for price and price
        $allowanceChargesPrice = [];
        $allowanceChargesPrice[] = (new AllowanceCharge())
            ->setChargeIndicator(false)
            ->setAllowanceChargeReason('discount')
            ->setAmount(0.00);
        $price = (new Price())
            ->setUnitCode(UnitCode::UNIT)
            ->setAllowanceCharges($allowanceChargesPrice)
            ->setPriceAmount(2);

        // Create invoice line tax total and invoice line
        $lineTaxTotal = (new TaxTotal())
            ->setTaxAmount(0.60)
            ->setRoundingAmount(4.60);
        $invoiceLine = (new InvoiceLine())
            ->setUnitCode("PCE")
            ->setId(1)
            ->setItem($productItem)
            ->setLineExtensionAmount(4)
            ->setPrice($price)
            ->setTaxTotal($lineTaxTotal)
            ->setInvoicedQuantity(2);
        $invoiceLines = [$invoiceLine];

        // Build the invoice object
        $invoice = (new Invoice())
            // Uncomment below if UBL extensions are needed: ->setUBLExtensions($ublExtensions)
            ->setUUID('3cf5ee18-ee25-44ea-a444-2c37ba7f28be')
            ->setId('SME00023')
            ->setIssueDate(new DateTime('2025-02-13'))
            ->setIssueTime(new DateTime('12:01:53'))
            ->setInvoiceType($invoiceType)
            ->setInvoiceCurrencyCode('SAR')
            ->setTaxCurrencyCode('SAR')
            ->setAdditionalDocumentReferences($additionalDocs)
            ->setAccountingSupplierParty($supplierCompany)
            ->setAccountingCustomerParty($supplierCustomer)
            ->setAllowanceCharges($allowanceCharges)
            ->setTaxTotal($taxTotal)
            ->setLegalMonetaryTotal($legalMonetaryTotal)
            ->setInvoiceLines($invoiceLines)
            ->setSignature($signature);

        // Generate invoice XML
        $generatorXml = new GeneratorInvoice();
        $outputXML = $generatorXml->invoice($invoice);

        // Assert that XML is generated
        $this->assertNotEmpty($outputXML, 'XML output should not be empty.');

        // Assert that XML contains the invoice ID
        $this->assertStringContainsString('<cbc:ID>SME00023</cbc:ID>', $outputXML, 'XML should contain the invoice ID.');

        // Validate that the XML is well-formed
        $dom = new DOMDocument();
        $loaded = $dom->loadXML($outputXML);
        $this->assertTrue($loaded, 'XML should be well-formed.');
    }
}