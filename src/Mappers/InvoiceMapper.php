<?php
namespace Saleh7\Zatca\Mappers;

use DateTime;
use Saleh7\Zatca\{
    Invoice, UBLExtensions, Signature, InvoiceType, TaxTotal, LegalMonetaryTotal, Delivery, AllowanceCharge, BillingReference
};
use Saleh7\Zatca\Mappers\Validators\InvoiceValidator;
// use Saleh7\Zatca\Mappers\Validators\InvoiceAmountValidator;

/**
 * Class InvoiceMapper
 *
 * Maps complete invoice data (provided as a JSON string or associative array)
 * into an Invoice object according to ZATCA specifications.
 *
 * The mapping process uses several dependent mappers to convert nested data sections,
 * such as supplier, customer, invoice lines, payment means, and additional documents.
 *
 * @package Saleh7\Zatca\Mappers
 */
class InvoiceMapper
{
    /**
     * @var SupplierMapper Mapper for supplier party data.
     */
    private SupplierMapper $supplierMapper;

    /**
     * @var CustomerMapper Mapper for customer party data.
     */
    private CustomerMapper $customerMapper;

    /**
     * @var InvoiceLineMapper Mapper for invoice line data.
     */
    private InvoiceLineMapper $invoiceLineMapper;

    /**
     * @var PaymentMeansMapper Mapper for payment means data.
     */
    private PaymentMeansMapper $paymentMeansMapper;

    /**
     * @var AdditionalDocumentMapper Mapper for additional document reference data.
     */
    private AdditionalDocumentMapper $additionalDocumentMapper;

    /**
     * InvoiceMapper constructor.
     *
     * Initializes all dependent mappers.
     */
    public function __construct()
    {
        $this->supplierMapper = new SupplierMapper();
        $this->customerMapper = new CustomerMapper();
        $this->invoiceLineMapper = new InvoiceLineMapper();
        $this->paymentMeansMapper = new PaymentMeansMapper();
        $this->additionalDocumentMapper = new AdditionalDocumentMapper();
    }

    /**
     * Map input data to an Invoice object.
     *
     * Accepts either a JSON string or an associative array. If the input is a JSON string,
     * it is decoded into an array. Validation of required fields is commented out,
     * but you may enable and customize validation as needed.
     *
     * @param array|string $data An associative array or JSON string of invoice data.
     * @return Invoice The mapped Invoice object.
     * @throws \InvalidArgumentException If invalid JSON data is provided.
     */
    public function mapToInvoice(array|string $data): Invoice
    {
        // If data is a JSON string, convert it to an array.
        if (is_string($data)) {
            $data = json_decode($data, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \InvalidArgumentException('Invalid JSON data provided');
            }
        }

        // Optionally, Validate the required invoice fields
        $validator = new InvoiceValidator();
        $validator->validate($data);

        // // Optionally, validate the invoice amounts and lines.
        // $validatorAmount = new InvoiceAmountValidator();
        // $validatorAmount->validateMonetaryTotals($data);
        // $validatorAmount->validateInvoiceLines($data['invoiceLines']);

        $invoice = new Invoice();

        // Map invoice sections using separate mappers.
        $invoice->setUBLExtensions($this->mapUBLExtensions($data['ublExtensions'] ?? []))
                ->setUUID($data['uuid'])
                ->setId($data['id'])
                ->setIssueDate($this->mapDateTime($data['issueDate']))
                ->setIssueTime($this->mapDateTime($data['issueTime'] ?? ''))
                ->setInvoiceType($this->mapInvoiceType($data['invoiceType'] ?? []))
                ->setNote($data['note'] ?? null)
                ->setlanguageID($data['languageID'] ?? 'en')
                ->setInvoiceCurrencyCode($data['currencyCode'] ?? 'SAR')
                ->setTaxCurrencyCode($data['taxCurrencyCode'] ?? 'SAR')
                ->setBillingReferences(
                    $this->mapBillingReferences($data['billingReferences'] ?? [])
                )
                ->setAdditionalDocumentReferences(
                    $this->additionalDocumentMapper->mapAdditionalDocuments($data['additionalDocuments'] ?? [])
                )
                ->setAccountingSupplierParty(
                    $this->supplierMapper->map($data['supplier'] ?? [])
                )
                ->setAccountingCustomerParty(
                    $this->customerMapper->map($data['customer'] ?? [])
                )
                ->setDelivery($this->mapDelivery($data['delivery'] ?? []))
                ->setPaymentMeans($this->paymentMeansMapper->map($data['paymentMeans'] ?? []))
                ->setAllowanceCharges($this->mapAllowanceCharge($data ?? []))
                ->setTaxTotal($this->mapTaxTotal($data['taxTotal'] ?? []))
                ->setLegalMonetaryTotal($this->mapLegalMonetaryTotal($data['legalMonetaryTotal'] ?? []))
                ->setInvoiceLines($this->invoiceLineMapper->mapInvoiceLines($data['invoiceLines'] ?? []))
                ->setSignature($this->mapSignature($data['signature'] ?? []));

        // Add additional notes if available.
        if (isset($data['notes'])) {
            foreach ($data['notes'] as $note) {
                $invoice->setNote($note['text'] ?? '', $note['languageId'] ?? null);
            }
        }

        return $invoice;
    }

    /**
     * Map UBLExtensions data to a UBLExtensions object.
     *
     * This method maps signature information and UBL extensions needed for the invoice.
     *
     * @param array $data The UBLExtensions data.
     * @return UBLExtensions The mapped UBLExtensions object.
     */
    private function mapUBLExtensions(array $data): UBLExtensions
    {
        // Create SignatureInformation and set its properties.
        $signatureInfo = (new \Saleh7\Zatca\SignatureInformation())
            ->setReferencedSignatureID($data['referencedSignatureId'] ?? "urn:oasis:names:specification:ubl:signature:Invoice")
            ->setID($data['id'] ?? 'urn:oasis:names:specification:ubl:signature:1');

        // Create UBLDocumentSignatures with the signature information.
        $ublDocSignatures = (new \Saleh7\Zatca\UBLDocumentSignatures())
            ->setSignatureInformation($signatureInfo);

        // Create ExtensionContent to hold the UBLDocumentSignatures.
        $extensionContent = (new \Saleh7\Zatca\ExtensionContent())
            ->setUBLDocumentSignatures($ublDocSignatures);

        // Create UBLExtension with the URI and extension content.
        $ublExtension = (new \Saleh7\Zatca\UBLExtension())
            ->setExtensionURI($data['extensionUri'] ?? 'urn:oasis:names:specification:ubl:dsig:enveloped:xades')
            ->setExtensionContent($extensionContent);

        return (new UBLExtensions())
            ->setUBLExtensions([$ublExtension]);
    }

    /**
     * Map Signature data to a Signature object.
     *
     * @param array $data The signature data.
     * @return Signature The mapped Signature object.
     */
    private function mapSignature(array $data): Signature
    {
        return (new Signature())
            ->setId($data['id'] ?? "urn:oasis:names:specification:ubl:signature:Invoice")
            ->setSignatureMethod($data['method'] ?? "urn:oasis:names:specification:ubl:dsig:enveloped:xades");
    }

    /**
     * Map InvoiceType data to an InvoiceType object.
     *
     * @param array $data The invoice type data.
     * @return InvoiceType The mapped InvoiceType object.
     */
    private function mapInvoiceType(array $data): InvoiceType
    {
        return (new InvoiceType())
            ->setInvoice($data['invoice'] ?? 'simplified')
            ->setInvoiceType($data['type'] ?? 'invoice')
            ->setIsThirdParty($data['isThirdParty'] ?? false)
            ->setIsNominal($data['isNominal'] ?? false)
            ->setIsExportInvoice($data['isExport'] ?? false)
            ->setIsSummary($data['isSummary'] ?? false)
            ->setIsSelfBilled($data['isSelfBilled'] ?? false);
    }

    /**
     * Map BillingReferences data to an array of BillingReference objects.
     *
     * @param array $data Array of billing references data.
     * @return BillingReference[] Array of mapped BillingReference objects.
     */
    private function mapBillingReferences(array $data): array
    {
        $billingReferences = [];
        foreach ($data as $billingData) {
            $billingReferences[] = (new BillingReference())
                ->setId($billingData['id'] ?? '');
        }
        return $billingReferences;
    }

    /**
     * Map AllowanceCharge data to an array of AllowanceCharge objects.
     *
     * @param array $data The invoice data containing allowance charges.
     * @return AllowanceCharge[] Array of mapped AllowanceCharge objects.
     */
    private function mapAllowanceCharge(array $data): array
    {
        $allowanceCharges = [];
        // Check if allowanceCharges is an array.
        if (!isset($data['allowanceCharges']) || !is_array($data['allowanceCharges'])) {
            return $allowanceCharges;
        }
        // Iterate over each allowance charge in the data.
        foreach ($data['allowanceCharges'] as $allowanceCharge) {
            $taxCategories = [];
            
            // Check if taxCategories is an array and iterate over it.
            if (isset($allowanceCharge['taxCategories']) && is_array($allowanceCharge['taxCategories'])) {
                foreach ($allowanceCharge['taxCategories'] as $taxCatData) {
                    $taxCategories[] = (new \Saleh7\Zatca\TaxCategory())
                        ->setPercent($taxCatData['percent'] ?? 15)
                        ->setTaxScheme(
                            (new \Saleh7\Zatca\TaxScheme())
                                ->setId($taxCatData['taxScheme']['id'] ?? "VAT")
                        );
                }
            }
            
            // Create the AllowanceCharge object with its tax categories.
            $allowanceCharges[] = (new \Saleh7\Zatca\AllowanceCharge())
                ->setChargeIndicator($allowanceCharge['isCharge'] ?? false)
                ->setAllowanceChargeReason($allowanceCharge['reason'] ?? 'discount')
                ->setAmount($allowanceCharge['amount'] ?? 0.00)
                ->setTaxCategory($taxCategories);
        }
        
        return $allowanceCharges;
    }

    /**
     * Map Delivery data to a Delivery object.
     *
     * @param array $data The delivery data.
     * @return \Saleh7\Zatca\Delivery The mapped Delivery object.
     */
    private function mapDelivery(array $data): \Saleh7\Zatca\Delivery
    {
        return (new \Saleh7\Zatca\Delivery())
            ->setActualDeliveryDate($data['actualDeliveryDate'] ?? null)
            ->setLatestDeliveryDate($data['latestDeliveryDate'] ?? null);
    }

    /**
     * Map TaxTotal data to a TaxTotal object.
     *
     * @param array $data The tax total data.
     * @return TaxTotal The mapped TaxTotal object.
     */
    private function mapTaxTotal(array $data): TaxTotal
    {
        $taxTotal = new TaxTotal();
        $taxTotal->setTaxAmount($data['taxAmount'] ?? 0);
        if (isset($data['subTotals']) && is_array($data['subTotals'])) {
            foreach ($data['subTotals'] as $subTotal) {
                // Extract tax category data from subTotal.
                $taxCategoryData = $subTotal['taxCategory'] ?? [];
                $percent = $taxCategoryData['percent'] ?? 15;
                $reasonCode = $taxCategoryData['reasonCode'] ?? null;
                $reason = $taxCategoryData['reason'] ?? null;
                
                // Build the TaxScheme object.
                $taxSchemeData = $taxCategoryData['taxScheme'] ?? [];
                $taxScheme = (new \Saleh7\Zatca\TaxScheme())
                    ->setId($taxSchemeData['id'] ?? "VAT");
                
                // Build the TaxCategory object using the extracted data.
                $taxCategory = (new \Saleh7\Zatca\TaxCategory())
                    ->setPercent($percent)
                    ->setTaxExemptionReasonCode($reasonCode)
                    ->setTaxExemptionReason($reason)
                    ->setTaxScheme($taxScheme);
                
                // Create the TaxSubTotal object.
                $taxSubTotal = (new \Saleh7\Zatca\TaxSubTotal())
                    ->setTaxableAmount($subTotal['taxableAmount'] ?? 0)
                    ->setTaxAmount($subTotal['taxAmount'] ?? 0)
                    ->setTaxCategory($taxCategory);
                
                $taxTotal->addTaxSubTotal($taxSubTotal);
            }
        }
        
        return $taxTotal;
    }

    /**
     * Map LegalMonetaryTotal data to a LegalMonetaryTotal object.
     *
     * @param array $data The legal monetary total data.
     * @return LegalMonetaryTotal The mapped LegalMonetaryTotal object.
     */
    private function mapLegalMonetaryTotal(array $data): LegalMonetaryTotal
    {
        return (new LegalMonetaryTotal())
            ->setLineExtensionAmount($data['lineExtensionAmount'] ?? 0)
            ->setTaxExclusiveAmount($data['taxExclusiveAmount'] ?? 0)
            ->setTaxInclusiveAmount($data['taxInclusiveAmount'] ?? 0)
            ->setPrepaidAmount($data['prepaidAmount'] ?? 0)
            ->setPayableAmount($data['payableAmount'] ?? 0)
            ->setAllowanceTotalAmount($data['allowanceTotalAmount'] ?? 0);
    }

    /**
     * Convert a date string to a DateTime object.
     *
     * @param string|null $dateTimeStr The date string.
     * @return DateTime The resulting DateTime object.
     */
    private function mapDateTime(?string $dateTimeStr): DateTime
    {
        if (empty($dateTimeStr)) {
            return new DateTime();
        }
        try {
            return new DateTime($dateTimeStr);
        } catch (\Exception $e) {
            return new DateTime();
        }
    }
}
