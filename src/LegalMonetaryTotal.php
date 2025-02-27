<?php
namespace Saleh7\Zatca;

use InvalidArgumentException;
use Sabre\Xml\Writer;
use Sabre\Xml\XmlSerializable;

/**
 * Class LegalMonetaryTotal
 *
 * Represents the legal monetary totals in an invoice for XML serialization.
 */
class LegalMonetaryTotal implements XmlSerializable
{
    /** @var float|null The total amount of line extensions. */
    private ?float $lineExtensionAmount = null;

    /** @var float|null The tax exclusive amount. */
    private ?float $taxExclusiveAmount = null;

    /** @var float|null The tax inclusive amount. */
    private ?float $taxInclusiveAmount = null;

    /** @var float The total allowance amount. Defaults to 0.0. */
    private ?float $allowanceTotalAmount = null;

    /** @var float The total charge amount. Defaults to 0.0. */
    private ?float $chargeTotalAmount = null;

    /** @var float|null The prepaid amount. */
    private ?float $prepaidAmount = null;

    /** @var float|null The payable amount. */
    private ?float $payableAmount = null;

    // Getters

    /**
     * Get the line extension amount.
     *
     * @return float|null
     */
    public function getLineExtensionAmount(): ?float
    {
        return $this->lineExtensionAmount;
    }

    /**
     * Get the tax exclusive amount.
     *
     * @return float|null
     */
    public function getTaxExclusiveAmount(): ?float
    {
        return $this->taxExclusiveAmount;
    }

    /**
     * Get the tax inclusive amount.
     *
     * @return float|null
     */
    public function getTaxInclusiveAmount(): ?float
    {
        return $this->taxInclusiveAmount;
    }

    /**
     * Get the total allowance amount.
     *
     * @return float
     */
    public function getAllowanceTotalAmount(): float
    {
        return $this->allowanceTotalAmount;
    }

    /**
     * Get the charge total amount.
     *
     * @return float
     */
    public function getChargeTotalAmount(): float
    {
        return $this->chargeTotalAmount;
    }

    /**
     * Get the prepaid amount.
     *
     * @return float|null
     */
    public function getPrepaidAmount(): ?float
    {
        return $this->prepaidAmount;
    }

    /**
     * Get the payable amount.
     *
     * @return float|null
     */
    public function getPayableAmount(): ?float
    {
        return $this->payableAmount;
    }

    // Setters

    /**
     * Set the line extension amount.
     *
     * @param float|null $lineExtensionAmount
     * @return self
     * @throws InvalidArgumentException if the amount is negative.
     */
    public function setLineExtensionAmount(?float $lineExtensionAmount): self
    {
        if ($lineExtensionAmount !== null && $lineExtensionAmount < 0) {
            throw new InvalidArgumentException('Line extension amount must be non-negative.');
        }
        $this->lineExtensionAmount = $lineExtensionAmount;
        return $this;
    }

    /**
     * Set the tax exclusive amount.
     *
     * @param float|null $taxExclusiveAmount
     * @return self
     * @throws InvalidArgumentException if the amount is negative.
     */
    public function setTaxExclusiveAmount(?float $taxExclusiveAmount): self
    {
        if ($taxExclusiveAmount !== null && $taxExclusiveAmount < 0) {
            throw new InvalidArgumentException('Tax exclusive amount must be non-negative.');
        }
        $this->taxExclusiveAmount = $taxExclusiveAmount;
        return $this;
    }

    /**
     * Set the tax inclusive amount.
     *
     * @param float|null $taxInclusiveAmount
     * @return self
     * @throws InvalidArgumentException if the amount is negative.
     */
    public function setTaxInclusiveAmount(?float $taxInclusiveAmount): self
    {
        if ($taxInclusiveAmount !== null && $taxInclusiveAmount < 0) {
            throw new InvalidArgumentException('Tax inclusive amount must be non-negative.');
        }
        $this->taxInclusiveAmount = $taxInclusiveAmount;
        return $this;
    }

    /**
     * Set the total allowance amount.
     *
     * @param float|null $allowanceTotalAmount
     * @return self
     * @throws InvalidArgumentException if the amount is negative.
     */
    public function setAllowanceTotalAmount(?float $allowanceTotalAmount): self
    {
        if ($allowanceTotalAmount !== null && $allowanceTotalAmount < 0) {
            throw new InvalidArgumentException('Allowance total amount must be non-negative.');
        }
        $this->allowanceTotalAmount = $allowanceTotalAmount ?? 0.0;
        return $this;
    }

    /**
     * Set the charge total amount.
     *
     * @param float|null $chargeTotalAmount
     * @return self
     * @throws InvalidArgumentException if the amount is negative.
     */
    public function setChargeTotalAmount(?float $chargeTotalAmount): self
    {
        if ($chargeTotalAmount !== null && $chargeTotalAmount < 0) {
            throw new InvalidArgumentException('Charge total amount must be non-negative.');
        }
        $this->chargeTotalAmount = $chargeTotalAmount ?? 0.0;
        return $this;
    }

    /**
     * Set the prepaid amount.
     *
     * @param float|null $prepaidAmount
     * @return self
     * @throws InvalidArgumentException if the amount is negative.
     */
    public function setPrepaidAmount(?float $prepaidAmount): self
    {
        if ($prepaidAmount !== null && $prepaidAmount < 0) {
            throw new InvalidArgumentException('Prepaid amount must be non-negative.');
        }
        $this->prepaidAmount = $prepaidAmount;
        return $this;
    }

    /**
     * Set the payable amount.
     *
     * @param float|null $payableAmount
     * @return self
     * @throws InvalidArgumentException if the amount is negative.
     */
    public function setPayableAmount(?float $payableAmount): self
    {
        if ($payableAmount !== null && $payableAmount < 0) {
            throw new InvalidArgumentException('Payable amount must be non-negative.');
        }
        $this->payableAmount = $payableAmount;
        return $this;
    }

    /**
     * Serializes this object to XML.
     *
     * The amounts are formatted to 2 decimal places and include a currency attribute.
     *
     * @param Writer $writer The XML writer.
     * @return void
     */
    public function xmlSerialize(Writer $writer): void
    {
        $currencyID = GeneratorInvoice::$currencyID;

        $elements = [];

        if ($this->lineExtensionAmount !== null) {
            $elements[] = [
                'name' => Schema::CBC . 'LineExtensionAmount',
                'value' => number_format($this->lineExtensionAmount, 2, '.', ''),
                'attributes' => ['currencyID' => $currencyID],
            ];
        }
        
        if ($this->taxExclusiveAmount !== null) {
            $elements[] = [
                'name' => Schema::CBC . 'TaxExclusiveAmount',
                'value' => number_format($this->taxExclusiveAmount, 2, '.', ''),
                'attributes' => ['currencyID' => $currencyID],
            ];
        }
        
        if ($this->taxInclusiveAmount !== null) {
            $elements[] = [
                'name' => Schema::CBC . 'TaxInclusiveAmount',
                'value' => number_format($this->taxInclusiveAmount, 2, '.', ''),
                'attributes' => ['currencyID' => $currencyID],
            ];
        }
        
        if ($this->allowanceTotalAmount !== null) {
            $elements[] = [
                'name' => Schema::CBC . 'AllowanceTotalAmount',
                'value' => number_format($this->allowanceTotalAmount, 2, '.', ''),
                'attributes' => ['currencyID' => $currencyID],
            ];
        }
        
        if ($this->chargeTotalAmount !== null) {
            $elements[] = [
                'name' => Schema::CBC . 'ChargeTotalAmount',
                'value' => number_format($this->chargeTotalAmount, 2, '.', ''),
                'attributes' => ['currencyID' => $currencyID],
            ];
        }
        
        if ($this->prepaidAmount !== null) {
            $elements[] = [
                'name' => Schema::CBC . 'PrepaidAmount',
                'value' => number_format($this->prepaidAmount, 2, '.', ''),
                'attributes' => ['currencyID' => $currencyID],
            ];
        }
        
        if ($this->payableAmount !== null) {
            $elements[] = [
                'name' => Schema::CBC . 'PayableAmount',
                'value' => number_format($this->payableAmount, 2, '.', ''),
                'attributes' => ['currencyID' => $currencyID],
            ];
        }
        
        $writer->write($elements);        
    }
}
