<?php
namespace Saleh7\Zatca;

use Sabre\Xml\Writer;
use Sabre\Xml\XmlSerializable;

use DateTime;
use InvalidArgumentException;

class Invoice implements XmlSerializable
{
    private $UBLExtensions;
    private $profileID = 'reporting:1.0';
    private $id;
    private $UUID;
    private $issueDate;
    private $issueTime;
    private $invoiceType;
    private $note;
    private $invoiceCurrencyCode = 'SAR';
    private $taxCurrencyCode = 'SAR';
    private $orderReference;
    private $billingReference;
    private $contract;
    private $additionalDocumentReferences;
    private $accountingSupplierParty;
    private $accountingCustomerParty;
    private $delivery;
    private $paymentMeans;
    private $allowanceCharges;
    private $taxTotal;
    private $legalMonetaryTotal;
    private $invoiceLines;
    private $signature;

    /**
     * @param UBLExtensions $UBLExtensions
     * @return Invoice
     */
    public function setUBLExtensions(UBLExtensions $UBLExtensions): Invoice
    {
        $this->UBLExtensions = $UBLExtensions;
        return $this;
    }
    /**
     * @param mixed $id
     * @return Invoice
     */
    public function setId(?string $id): Invoice
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @param mixed $UUID
     * @return Invoice
     */
    public function setUUID(?string $UUID): Invoice
    {
        $this->UUID = $UUID;
        return $this;
    }

    /**
     * @param DateTime $issueDate
     * @return Invoice
     */
    public function setIssueDate(DateTime $issueDate): Invoice
    {
        $this->issueDate = $issueDate;
        return $this;
    }

    /**
     * @param DateTime $issueDate
     * @return Invoice
     */
    public function setIssueTime(DateTime $issueTime): Invoice
    {
        $this->issueTime = $issueTime;
        return $this;
    }

    /**
     * @param InvoiceType $invoiceType
     * @return Invoice
     */
    public function setInvoiceType(InvoiceType $invoiceType): Invoice
    {
        $this->invoiceType = $invoiceType;
        return $this;
    }

    /**
     * @param mixed $currencyCode
     * @return Invoice
     */
    public function setInvoiceCurrencyCode(string $currencyCode = 'SAR'): Invoice
    {
        $this->invoiceCurrencyCode = $currencyCode;
        return $this;
    }
    /**
     * @param mixed $currencyCode
     * @return Invoice
     */
    public function setTaxCurrencyCode(string $currencyCode = 'SAR'): Invoice
    {
        $this->taxCurrencyCode = $currencyCode;
        return $this;
    }
    /**
     * @param OrderReference $orderReference
     * @return OrderReference
     */
    public function setOrderReference(OrderReference $orderReference): Invoice
    {
        $this->orderReference = $orderReference;
        return $this;
    }

    /**
     * @param BillingReference $billingReference
     * @return BillingReference
     */
    public function setBillingReference(BillingReference $billingReference): Invoice
    {
        $this->billingReference = $billingReference;
        return $this;
    }

    /**
     * @param string $contract
     * @return Invoice
     */
    public function setContract(Contract $contract): Invoice
    {
        $this->contract = $contract;
        return $this;
    }

    /**
     * @param AdditionalDocumentReference[] $additionalDocumentReferences
     * @return Invoice
     */
    public function setAdditionalDocumentReferences(array $additionalDocumentReferences): Invoice
    {
        $this->additionalDocumentReferences = $additionalDocumentReferences;
        return $this;
    }

    /**
     * @param Party $accountingSupplierParty
     * @return Invoice
     */
    public function setAccountingSupplierParty(Party $accountingSupplierParty): Invoice
    {
        $this->accountingSupplierParty = $accountingSupplierParty;
        return $this;
    }

    /**
     * @param Party $accountingCustomerParty
     * @return Invoice
     */
    public function setAccountingCustomerParty(Party $accountingCustomerParty): Invoice
    {
        $this->accountingCustomerParty = $accountingCustomerParty;
        return $this;
    }

    /**
     * @param Delivery $delivery
     * @return Invoice
     */
    public function setDelivery(Delivery $delivery): Invoice
    {
        $this->delivery = $delivery;
        return $this;
    }

    /**
     * @param PaymentMeans $paymentMeans
     * @return Invoice
     */
    public function setPaymentMeans(PaymentMeans $paymentMeans): Invoice
    {
        $this->paymentMeans = $paymentMeans;
        return $this;
    }

    /**
     * @param AllowanceCharge[] $allowanceCharges
     * @return Invoice
     */
    public function setAllowanceCharges(array $allowanceCharges): Invoice
    {
        $this->allowanceCharges = $allowanceCharges;
        return $this;
    }

    /**
     * @param Signature
     * @return Invoice
     */
    public function Signature(Signature $signature): Invoice
    {
        $this->signature = $signature;
        return $this;
    }

    /**
     * @param mixed $currencyCode
     * @return Invoice
     */
    public function setDocumentCurrencyCode(string $currencyCode = 'SAR'): Invoice
    {
        $this->documentCurrencyCode = $currencyCode;
        return $this;
    }

    /**
     * @param string $note
     * @return Invoice
     */
    public function setNote(string $note)
    {
        $this->note = $note;
        return $this;
    }

    /**
     * @param TaxTotal $taxTotal
     * @return Invoice
     */
    public function setTaxTotal(TaxTotal $taxTotal): Invoice
    {
        $this->taxTotal = $taxTotal;
        return $this;
    }

    /**
     * @param LegalMonetaryTotal $legalMonetaryTotal
     * @return Invoice
     */
    public function setLegalMonetaryTotal(LegalMonetaryTotal $legalMonetaryTotal): Invoice
    {
        $this->legalMonetaryTotal = $legalMonetaryTotal;
        return $this;
    }

    /**
     * @param InvoiceLine[] $invoiceLines
     * @return Invoice
     */
    public function setInvoiceLines(array $invoiceLines): Invoice
    {
        $this->invoiceLines = $invoiceLines;
        return $this;
    }

    /**
     * The validate function that is called during xml writing to valid the data of the object.
     *
     * @return void
     * @throws InvalidArgumentException An error with information about required data that is missing to write the XML
     */
    public function validate()
    {
        if ($this->id === null) {
            throw new InvalidArgumentException('Missing invoice id');
        }


        if (!$this->issueDate instanceof DateTime) {
            throw new InvalidArgumentException('Invalid invoice issueDate');
        }

        if (!$this->issueTime instanceof DateTime) {
            throw new InvalidArgumentException('Invalid invoice issueTime');
        }

        if ($this->accountingSupplierParty === null) {
            throw new InvalidArgumentException('Missing invoice accountingSupplierParty');
        }

        if ($this->accountingCustomerParty === null) {
            throw new InvalidArgumentException('Missing invoice accountingCustomerParty');
        }

        if ($this->additionalDocumentReferences === null) {
            throw new InvalidArgumentException('Missing invoice additionalDocumentReferences');
        }

        if ($this->invoiceLines === null) {
            throw new InvalidArgumentException('Missing invoice lines');
        }

        if ($this->legalMonetaryTotal === null) {
            throw new InvalidArgumentException('Missing invoice LegalMonetaryTotal');
        }
    }

    /**
     * The xmlSerialize method is called during xml writing.
     * @param Writer $writer
     * @return void
     */
    public function xmlSerialize(Writer $writer): void
    {
        $this->validate();
         // UBLExtensions

         if ($this->UBLExtensions !== null) {
             $writer->write([
                 Schema::EXT . 'UBLExtensions' => $this->UBLExtensions
             ]);
        }
         // profileID
        if ($this->profileID !== null) {
            $writer->write( [ Schema::CBC . 'ProfileID' => $this->profileID ] );
        }
         // id
        if ($this->id !== null) {
            $writer->write( [ Schema::CBC . 'ID' => $this->id ] );
        }
         // UUID
        if ($this->UUID !== null) {
            $writer->write( [ Schema::CBC . 'UUID' => $this->UUID ] );
        }
         // issueDate
        if ($this->issueDate !== null) {
            $writer->write( [ Schema::CBC . 'IssueDate' => $this->issueDate->format('Y-m-d') ] );
        }
         // issueTime
        if ($this->issueTime !== null) {
            $writer->write( [ Schema::CBC . 'IssueTime' => $this->issueTime->format('H:i:s') ] );
        }
         // invoiceType
         if($this->invoiceType !== null){
             $writer->write([$this->invoiceType
                 ]
             );
         }
          // note
         if ($this->note !== null) {
             $writer->write( [ Schema::CBC . 'Note' => $this->note ] );
         }
         // DocumentCurrencyCode
         if($this->invoiceCurrencyCode !== null){
             $writer->write([Schema::CBC . 'DocumentCurrencyCode' => $this->invoiceCurrencyCode]);
         }
         // taxCurrencyCode
         if($this->taxCurrencyCode !== null){
             $writer->write( [ Schema::CBC . 'TaxCurrencyCode' => $this->taxCurrencyCode ] );
         }
          // OrderReference
         if ($this->orderReference != null) {
             $writer->write( [ Schema::CAC . 'OrderReference' => $this->orderReference ] );
         }
          // BillingReference
         if ($this->billingReference != null) {
             $writer->write( [ Schema::CAC . 'BillingReference' => $this->billingReference ] );
         }
          // ContractDocumentReference
         if ($this->contract !== null) {
             $writer->write([
                 Schema::CAC . 'ContractDocumentReference' => $this->contract,
             ]);
         }
          // AdditionalDocumentReference
         if($this->additionalDocumentReferences !== null){
             foreach ($this->additionalDocumentReferences as $additionalDocumentReference) {
                 $writer->write([
                     Schema::CAC . 'AdditionalDocumentReference' => $additionalDocumentReference
                 ]);
             }
         }
          // Signature
         if ($this->signature !== null) {
             $writer->write([
                 Schema::CAC . "Signature" => $this->signature
             ]);
         }
          // AccountingSupplierParty / Party
         if ($this->accountingSupplierParty != null) {
             $writer->write( [ Schema::CAC . 'AccountingSupplierParty' => [
                 Schema::CAC . "Party" => $this->accountingSupplierParty
                 ] ] );
         }
          // AccountingCustomerParty / Party
         if ($this->accountingCustomerParty != null) {
             $writer->write( [ Schema::CAC . 'AccountingCustomerParty' => [
                 Schema::CAC . "Party" => $this->accountingCustomerParty
                 ] ] );
         }
          // Delivery
         if ($this->delivery != null) {
             $writer->write( [ Schema::CAC . 'Delivery' => $this->delivery ] );
         }
          // PaymentMeans
         if ($this->paymentMeans !== null) {
             $writer->write( [ Schema::CAC . 'PaymentMeans' => $this->paymentMeans ] );
         }
          // AllowanceCharge
         if ($this->allowanceCharges !== null) {
             foreach ($this->allowanceCharges as $allowanceCharge) {
                 $writer->write([
                     Schema::CAC . 'AllowanceCharge' => $allowanceCharge
                 ]);
             }
         }
          // TaxTotal
         if ($this->taxTotal !== null) {

             if ($this->taxTotal->taxAmount !== null) {
                 $writer->write( [ Schema::CAC . 'TaxTotal' => [
                     Schema::CBC . "TaxAmount" => [
                         'value' => number_format($this->taxTotal->taxAmount, 1, '.', ''),
                         'attributes' => [
                             'currencyID' => GeneratorInvoice::$currencyID
                  ]]]] );
             }

             $writer->write([
                 Schema::CAC . 'TaxTotal' => $this->taxTotal
             ]);
         }
          // LegalMonetaryTotal
         if ($this->legalMonetaryTotal !== null) {
             $writer->write([
                 Schema::CAC . 'LegalMonetaryTotal' => $this->legalMonetaryTotal
             ]);
         }
          // InvoiceLine
          if($this->invoiceLines){
              foreach ($this->invoiceLines as $invoiceLine) {
                  $writer->write([
                      Schema::CAC . 'InvoiceLine' => $invoiceLine
                  ]);
              }
          }
    }
}
