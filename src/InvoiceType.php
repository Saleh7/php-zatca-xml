<?php
namespace Saleh7\Zatca;

use Sabre\Xml\Writer;
use Sabre\Xml\XmlSerializable;

class InvoiceType implements XmlSerializable
{
    private $invoice;
    private $invoiceType;


    /**
     * @param mixed $invoiceType
     * @return InvoiceType
     */
    public function setInvoice(?string $invoice): InvoiceType
    {
        $this->invoice = $invoice;
        return $this;
    }

    /**
     * @param mixed $invoiceType
     * @return InvoiceType
     */
    public function setInvoiceType(?string $invoiceType): InvoiceType
    {
        $this->invoiceType = $invoiceType;
        return $this;
    }

    /**
     * The xmlSerialize method is called during xml writing.
     *
     * @param Writer $writer
     * @return void
     */
    public function xmlSerialize(Writer $writer): void
    {
        if($this->invoiceType == 'Invoice'){
            $invoiceTypeCode = InvoiceTypeCode::INVOICE;
        }elseif ($this->invoiceType == 'Debit') {
            $invoiceTypeCode = InvoiceTypeCode::DEBIT_NOTE;
        }elseif ($this->invoiceType == 'Credit') {
            $invoiceTypeCode = InvoiceTypeCode::CREDIT_NOTE;
        }

         // Invoice
        if($this->invoice == 'Invoice'){
            if($this->invoiceType == 'Invoice'){
                $invoiceType = InvoiceTypeCode::TAX_INVOICE;
            }elseif ($this->invoiceType == 'Debit') {
                $invoiceType = InvoiceTypeCode::TAX_DEBIT_NOTE;
            }elseif ($this->invoiceType == 'Credit') {
                $invoiceType = InvoiceTypeCode::TAX_CREDIT_NOTE;
            }
            $writer->write([
                [
                    "name" => Schema::CBC . 'InvoiceTypeCode',
                    "value" => $invoiceTypeCode,
                    "attributes" => [
                        "name" => $invoiceType
                    ]
                ],
            ]);
        }
         // Simplified
        if($this->invoice == 'Simplified'){
            if($this->invoiceType == 'Invoice'){
                $invoiceType = InvoiceTypeCode::SIMPLIFIED_INVOICE;
            }elseif ($this->invoiceType == 'Debit') {
                $invoiceType = InvoiceTypeCode::SIMPLIFIED_DEBIT_NOTE;
            }elseif ($this->invoiceType == 'Credit') {
                $invoiceType = InvoiceTypeCode::SIMPLIFIED_CREDIT_NOTE;
            }
            $writer->write([
                [
                    "name" => Schema::CBC . 'InvoiceTypeCode',
                    "value" => $invoiceTypeCode,
                    "attributes" => [
                        "name" => $invoiceType
                    ]
                ],
            ]);
        }
    }
}
