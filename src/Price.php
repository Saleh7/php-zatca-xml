<?php
namespace Saleh7\Zatca;

use InvalidArgumentException;
use Sabre\Xml\Writer;
use Sabre\Xml\XmlSerializable;

/**
 * Class Price
 *
 * Represents the price details for an invoice line.
 */
class Price implements XmlSerializable
{
    /** @var float|null Price amount. */
    private ?float $priceAmount = null;

    /** @var float|null Base quantity. */
    private ?float $baseQuantity = null;

    /** @var string Unit code (default: UnitCode::UNIT). */
    private string $unitCode;

    /** @var AllowanceCharge[]|null Array of allowance charge objects. */
    private ?array $allowanceCharges = null;

    /**
     * Price constructor.
     */
    public function __construct()
    {
        $this->unitCode = UnitCode::UNIT;
    }

    /**
     * Set the price amount.
     *
     * @param float|null $priceAmount
     * @return self
     * @throws InvalidArgumentException if price amount is negative.
     */
    public function setPriceAmount(?float $priceAmount): self
    {
        if ($priceAmount !== null && $priceAmount < 0) {
            throw new InvalidArgumentException('Price amount must be non-negative.');
        }
        $this->priceAmount = $priceAmount;
        return $this;
    }

    /**
     * Get the price amount.
     *
     * @return float|null
     */
    public function getPriceAmount(): ?float
    {
        return $this->priceAmount;
    }

    /**
     * Set the base quantity.
     *
     * @param float|null $baseQuantity
     * @return self
     * @throws InvalidArgumentException if base quantity is negative.
     */
    public function setBaseQuantity(?float $baseQuantity): self
    {
        if ($baseQuantity !== null && $baseQuantity < 0) {
            throw new InvalidArgumentException('Base quantity must be non-negative.');
        }
        $this->baseQuantity = $baseQuantity;
        return $this;
    }

    /**
     * Get the base quantity.
     *
     * @return float|null
     */
    public function getBaseQuantity(): ?float
    {
        return $this->baseQuantity;
    }

    /**
     * Set the unit code.
     *
     * @param string|null $unitCode
     * @return self
     * @throws InvalidArgumentException if unit code is empty.
     */
    public function setUnitCode(?string $unitCode): self
    {
        if ($unitCode !== null && trim($unitCode) === '') {
            throw new InvalidArgumentException('Unit code cannot be empty.');
        }
        $this->unitCode = $unitCode ?? $this->unitCode;
        return $this;
    }

    /**
     * Get the unit code.
     *
     * @return string
     */
    public function getUnitCode(): string
    {
        return $this->unitCode;
    }

    /**
     * Get the allowance charges.
     *
     * @return AllowanceCharge[]|null
     */
    public function getAllowanceCharges(): ?array
    {
        return $this->allowanceCharges;
    }

    /**
     * Set the allowance charges.
     *
     * @param AllowanceCharge[]|null $allowanceCharges
     * @return self
     */
    public function setAllowanceCharges(?array $allowanceCharges): self
    {
        $this->allowanceCharges = $allowanceCharges;
        return $this;
    }

    /**
     * Serializes this object to XML.
     *
     * @param Writer $writer The XML writer.
     * @return void
     * @throws InvalidArgumentException if price amount is not set.
     */
    public function xmlSerialize(Writer $writer): void
    {
        if ($this->priceAmount === null) {
            throw new InvalidArgumentException('Price amount must be set.');
        }
        $writer->write([
            [
                'name'       => Schema::CBC . 'PriceAmount',
                'value'      => number_format($this->priceAmount, 4, '.', ''),
                'attributes' => [
                    'currencyID' => GeneratorInvoice::$currencyID,
                ],
            ],
        ]);
        if ($this->baseQuantity !== null) {
            $writer->write([
                'name'       => Schema::CBC . 'BaseQuantity',
                'value'      => number_format($this->baseQuantity, 4, '.', ''),
                'attributes' => [
                    'unitCode' => $this->unitCode,
                ],
            ]);
        }
        if ($this->allowanceCharges !== null) {
            foreach ($this->allowanceCharges as $allowanceCharge) {
                $writer->write([
                    Schema::CAC . 'AllowanceCharge' => $allowanceCharge,
                ]);
            }
        }
    }
}
