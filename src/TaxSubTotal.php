<?php
namespace Saleh7\Zatca;

use Sabre\Xml\Writer;
use Sabre\Xml\XmlSerializable;

use InvalidArgumentException;

class TaxSubTotal implements XmlSerializable
{
    private $taxableAmount;
    private $taxAmount;
    private $taxCategory;
    private $percent;

    /**
     * @param mixed $taxableAmount
     * @return TaxSubTotal
     */
    public function setTaxableAmount(?float $taxableAmount): TaxSubTotal
    {
        $this->taxableAmount = $taxableAmount;
        return $this;
    }

    /**
     * @param mixed $taxAmount
     * @return TaxSubTotal
     */
    public function setTaxAmount(?float $taxAmount): TaxSubTotal
    {
        $this->taxAmount = $taxAmount;
        return $this;
    }

    /**
     * @param TaxCategory $taxCategory
     * @return TaxSubTotal
     */
    public function setTaxCategory(TaxCategory $taxCategory): TaxSubTotal
    {
        $this->taxCategory = $taxCategory;
        return $this;
    }

    /**
     * @param float $percent
     * @return TaxSubTotal
     */
    public function setPercent(?float $percent): TaxSubTotal
    {
        $this->percent = $percent;
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
        if ($this->taxableAmount === null) {
            throw new InvalidArgumentException('Missing taxsubtotal taxableAmount');
        }

        if ($this->taxAmount === null) {
            throw new InvalidArgumentException('Missing taxsubtotal taxamount');
        }

        if ($this->taxCategory === null) {
            throw new InvalidArgumentException('Missing taxsubtotal taxcategory');
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
                'name' => Schema::CBC . 'TaxableAmount',
                'value' => number_format($this->taxableAmount, 2, '.', ''),
                'attributes' => [
                    'currencyID' => GeneratorInvoice::$currencyID
                ]
            ],
            [
                'name' => Schema::CBC . 'TaxAmount',
                'value' => number_format($this->taxAmount, 2, '.', ''),
                'attributes' => [
                    'currencyID' => GeneratorInvoice::$currencyID
                ]
            ]
        ]);

        if ($this->percent !== null) {
            $writer->write([
                Schema::CBC . 'Percent' => $this->percent
            ]);
        }

        $writer->write([
            Schema::CAC . 'TaxCategory' => $this->taxCategory
        ]);
    }
}
