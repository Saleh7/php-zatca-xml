<?php
namespace Saleh7\Zatca;

use Sabre\Xml\Writer;
use Sabre\Xml\XmlSerializable;

use InvalidArgumentException;

class TaxTotal implements XmlSerializable
{
    public $taxAmount;
    private $roundingAmount;
    private $taxSubTotals = [];

    /**
     * @param mixed $taxAmount
     * @return TaxTotal
     */
    public function setTaxAmount(?float $taxAmount): TaxTotal
    {
        $this->taxAmount = $taxAmount;
        return $this;
    }

    /**
     * @param mixed $taxAmount
     * @return TaxTotal
     */
    public function setRoundingAmount(?float $roundingAmount): TaxTotal
    {
        $this->roundingAmount = $roundingAmount;
        return $this;
    }

    /**
     * @param TaxSubTotal $taxSubTotal
     * @return TaxTotal
     */
    public function addTaxSubTotal(TaxSubTotal $taxSubTotal): TaxTotal
    {
        $this->taxSubTotals[] = $taxSubTotal;
        return $this;
    }

    /**
     * The validate function that is called during xml writing to valid the data of the object.
     *
     * @throws InvalidArgumentException An error with information about required data that is missing to write the XML
     * @return void
     */
    public function validate()
    {
        if ($this->taxAmount === null) {
            throw new InvalidArgumentException('Missing taxtotal taxamount');
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

        $writer->write([
            [
                'name' => Schema::CBC . 'TaxAmount',
                'value' => number_format($this->taxAmount, 2, '.', ''),
                'attributes' => [
                    'currencyID' => GeneratorInvoice::$currencyID
                ]
            ],
        ]);
        if ($this->roundingAmount !== null) {
            $writer->write([
                [
                    'name' => Schema::CBC . 'RoundingAmount',
                    'value' => number_format($this->roundingAmount, 2, '.', ''),
                    'attributes' => [
                        'currencyID' => GeneratorInvoice::$currencyID
                    ]
                ],
            ]);
        }


        foreach ($this->taxSubTotals as $taxSubTotal) {
            $writer->write([Schema::CAC . 'TaxSubtotal' => $taxSubTotal]);
        }
    }
}
