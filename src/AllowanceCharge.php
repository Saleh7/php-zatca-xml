<?php
namespace Saleh7\Zatca;

use InvalidArgumentException;
use Sabre\Xml\Writer;
use Sabre\Xml\XmlSerializable;

/**
 * Class AllowanceCharge
 *
 * Represents an allowance or charge for XML serialization.
 */
class AllowanceCharge implements XmlSerializable
{
    /** @var bool Indicates if this is a charge (true) or an allowance (false). */
    private bool $chargeIndicator;

    /** @var string|null Reason code for the allowance/charge. */
    private ?string $allowanceChargeReasonCode = null;

    /** @var string|null Reason description for the allowance/charge. */
    private ?string $allowanceChargeReason = null;

    /** @var int|null Multiplier factor numeric value. */
    private ?int $multiplierFactorNumeric = null;

    /** @var float|null Base amount. */
    private ?float $baseAmount = null;

    /** @var float|null Amount value. */
    private ?float $amount = null;

    /** @var TaxTotal|null Tax total information. */
    private ?TaxTotal $taxTotal = null;

     /** @var array|null List of tax categories. */
    private ?array $taxCategory = null;

    /**
     * Get the charge indicator.
     *
     * @return bool
     */
    public function isChargeIndicator(): bool
    {
        return $this->chargeIndicator;
    }

    /**
     * Set the charge indicator.
     *
     * @param bool $chargeIndicator
     * @return self
     */
    public function setChargeIndicator(bool $chargeIndicator): self
    {
        $this->chargeIndicator = $chargeIndicator;
        return $this;
    }

    /**
     * Get the allowance charge reason code.
     *
     * @return string|null
     */
    public function getAllowanceChargeReasonCode(): ?string
    {
        return $this->allowanceChargeReasonCode;
    }

    /**
     * Set the allowance charge reason code.
     *
     * @param string|null $allowanceChargeReasonCode Must be non-negative if numeric.
     * @return self
     * @throws InvalidArgumentException
     */
    public function setAllowanceChargeReasonCode(?string $allowanceChargeReasonCode): self
    {
        if ($allowanceChargeReasonCode !== null && is_numeric($allowanceChargeReasonCode) && (float)$allowanceChargeReasonCode < 0) {
            throw new InvalidArgumentException('Allowance charge reason code must be non-negative.');
        }
        $this->allowanceChargeReasonCode = $allowanceChargeReasonCode;
        return $this;
    }

    /**
     * Get the allowance charge reason.
     *
     * @return string|null
     */
    public function getAllowanceChargeReason(): ?string
    {
        return $this->allowanceChargeReason;
    }

    /**
     * Set the allowance charge reason.
     *
     * @param string|null $allowanceChargeReason Must not be empty if provided.
     * @return self
     * @throws InvalidArgumentException
     */
    public function setAllowanceChargeReason(?string $allowanceChargeReason): self
    {
        if ($allowanceChargeReason !== null && trim($allowanceChargeReason) === '') {
            throw new InvalidArgumentException('Allowance charge reason cannot be an empty string.');
        }
        $this->allowanceChargeReason = $allowanceChargeReason;
        return $this;
    }

    /**
     * Get the multiplier factor numeric.
     *
     * @return int|null
     */
    public function getMultiplierFactorNumeric(): ?int
    {
        return $this->multiplierFactorNumeric;
    }

    /**
     * Set the multiplier factor numeric.
     *
     * @param int|null $multiplierFactorNumeric Must be non-negative.
     * @return self
     * @throws InvalidArgumentException
     */
    public function setMultiplierFactorNumeric(?int $multiplierFactorNumeric): self
    {
        if ($multiplierFactorNumeric !== null && $multiplierFactorNumeric < 0) {
            throw new InvalidArgumentException('Multiplier factor numeric must be non-negative.');
        }
        $this->multiplierFactorNumeric = $multiplierFactorNumeric;
        return $this;
    }

    /**
     * Get the base amount.
     *
     * @return float|null
     */
    public function getBaseAmount(): ?float
    {
        return $this->baseAmount;
    }

    /**
     * Set the base amount.
     *
     * @param float|null $baseAmount Must be non-negative.
     * @return self
     * @throws InvalidArgumentException
     */
    public function setBaseAmount(?float $baseAmount): self
    {
        if ($baseAmount !== null && $baseAmount < 0) {
            throw new InvalidArgumentException('Base amount must be non-negative.');
        }
        $this->baseAmount = $baseAmount;
        return $this;
    }

    /**
     * Get the amount.
     *
     * @return float|null
     */
    public function getAmount(): ?float
    {
        return $this->amount;
    }

    /**
     * Set the amount.
     *
     * @param float|null $amount Must be non-negative.
     * @return self
     * @throws InvalidArgumentException
     */
    public function setAmount(?float $amount): self
    {
        if ($amount !== null && $amount < 0) {
            throw new InvalidArgumentException('Amount must be non-negative.');
        }
        $this->amount = $amount;
        return $this;
    }

    /**
     * Get the tax category.
     *
     * @return array|null
     */
    public function getTaxCategory(): ?TaxCategory
    {
        return $this->taxCategory;
    }

    /**
     * Set the tax category.
     *
     * @param TaxCategory|array|null $taxCategory
     * @return self
     */
    public function setTaxCategory(TaxCategory|array|null $taxCategory): self
    {
        if ($taxCategory instanceof TaxCategory) {
            $this->taxCategory = [$taxCategory];
        } else {
            $this->taxCategory = $taxCategory;
        }
        return $this;
    }

    /**
     * Get the tax total.
     *
     * @return TaxTotal|null
     */
    public function getTaxTotal(): ?TaxTotal
    {
        return $this->taxTotal;
    }

    /**
     * Set the tax total.
     *
     * @param TaxTotal|null $taxTotal
     * @return self
     */
    public function setTaxTotal(?TaxTotal $taxTotal): self
    {
        $this->taxTotal = $taxTotal;
        return $this;
    }

    /**
     * Serializes this object to XML.
     *
     * @param Writer $writer The XML writer.
     * @return void
     */
    public function xmlSerialize(Writer $writer): void
    {
        // Write ChargeIndicator as "true" or "false"
        $writer->write([
            Schema::CBC . 'ChargeIndicator' => $this->chargeIndicator ? 'true' : 'false',
        ]);

        if ($this->allowanceChargeReasonCode !== null) {
            $writer->write([
                Schema::CBC . 'AllowanceChargeReasonCode' => $this->allowanceChargeReasonCode,
            ]);
        }

        if ($this->allowanceChargeReason !== null) {
            $writer->write([
                Schema::CBC . 'AllowanceChargeReason' => $this->allowanceChargeReason,
            ]);
        }

        if ($this->multiplierFactorNumeric !== null) {
            $writer->write([
                Schema::CBC . 'MultiplierFactorNumeric' => $this->multiplierFactorNumeric,
            ]);
        }

        if ($this->amount !== null) {
            $writer->write([
                [
                    'name'       => Schema::CBC . 'Amount',
                    'value'      => number_format($this->amount, 2, '.', ''),
                    'attributes' => [
                        'currencyID' => GeneratorInvoice::$currencyID,
                    ],
                ],
            ]);
        }

        if ($this->taxCategory !== null) {
            foreach($this->taxCategory as $taxCategory){
                $writer->write([
                    Schema::CAC . 'TaxCategory' => $taxCategory
                ]);
            }
        }

        if ($this->taxTotal !== null) {
            $writer->write([
                Schema::CAC . 'TaxTotal' => $this->taxTotal,
            ]);
        }

        if ($this->baseAmount !== null) {
            $writer->write([
                [
                    'name'       => Schema::CBC . 'BaseAmount',
                    'value'      => number_format($this->baseAmount, 2, '.', ''),
                    'attributes' => [
                        'currencyID' => GeneratorInvoice::$currencyID,
                    ],
                ],
            ]);
        }
    }
}