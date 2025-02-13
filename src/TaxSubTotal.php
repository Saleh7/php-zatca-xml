<?php
namespace Saleh7\Zatca;

use Sabre\Xml\Writer;
use Sabre\Xml\XmlSerializable;
use InvalidArgumentException;

/**
 * Class TaxSubTotal
 *
 * Represents the subtotal for tax calculations with XML serialization.
 */
class TaxSubTotal implements XmlSerializable
{
    /** @var float|null Taxable amount. */
    private ?float $taxableAmount = null;

    /** @var float|null Tax amount. */
    private ?float $taxAmount = null;

    /** @var TaxCategory|null Tax category. */
    private ?TaxCategory $taxCategory = null;

    /** @var float|null Tax percentage. */
    private ?float $percent = null;

    /**
     * Set the taxable amount.
     *
     * @param float|null $taxableAmount
     * @return self
     */
    public function setTaxableAmount(?float $taxableAmount): self
    {
        $this->taxableAmount = $taxableAmount;
        return $this;
    }

    /**
     * Set the tax amount.
     *
     * @param float|null $taxAmount
     * @return self
     */
    public function setTaxAmount(?float $taxAmount): self
    {
        $this->taxAmount = $taxAmount;
        return $this;
    }

    /**
     * Set the tax category.
     *
     * @param TaxCategory $taxCategory
     * @return self
     */
    public function setTaxCategory(TaxCategory $taxCategory): self
    {
        $this->taxCategory = $taxCategory;
        return $this;
    }

    /**
     * Set the tax percentage.
     *
     * @param float|null $percent
     * @return self
     */
    public function setPercent(?float $percent): self
    {
        $this->percent = $percent;
        return $this;
    }

    /**
     * Validates that the required data is present.
     *
     * @return void
     * @throws InvalidArgumentException if taxableAmount, taxAmount, or taxCategory is missing.
     */
    public function validate(): void
    {
        if ($this->taxableAmount === null) {
            throw new InvalidArgumentException('Missing taxsubtotal taxableAmount.');
        }
        if ($this->taxAmount === null) {
            throw new InvalidArgumentException('Missing taxsubtotal taxAmount.');
        }
        if ($this->taxCategory === null) {
            throw new InvalidArgumentException('Missing taxsubtotal taxCategory.');
        }
    }

    /**
     * Serializes the TaxSubTotal object to XML.
     *
     * @param Writer $writer The XML writer instance.
     * @return void
     */
    public function xmlSerialize(Writer $writer): void
    {
        $this->validate();

        $currencyID = GeneratorInvoice::$currencyID;

        // Write TaxableAmount and TaxAmount elements
        $writer->write([
            [
                'name' => Schema::CBC . 'TaxableAmount',
                'value' => number_format($this->taxableAmount, 2, '.', ''),
                'attributes' => [
                    'currencyID' => $currencyID,
                ],
            ],
            [
                'name' => Schema::CBC . 'TaxAmount',
                'value' => number_format($this->taxAmount, 2, '.', ''),
                'attributes' => [
                    'currencyID' => $currencyID,
                ],
            ],
        ]);

        // Optionally write the Percent element
        if ($this->percent !== null) {
            $writer->write([
                Schema::CBC . 'Percent' => $this->percent
            ]);
        }

        // Write the TaxCategory element
        $writer->write([
            Schema::CAC . 'TaxCategory' => $this->taxCategory
        ]);
    }
}
