<?php
namespace Saleh7\Zatca;

use InvalidArgumentException;
use Sabre\Xml\Writer;
use Sabre\Xml\XmlSerializable;

/**
 * Class ClassifiedTaxCategory
 *
 * Represents a classified tax category for XML serialization.
 */
class ClassifiedTaxCategory implements XmlSerializable
{
    /** @var string|null Identifier for the tax category. */
    private ?string $id = null;

    /** @var string|null Name of the tax category. */
    private ?string $name = null;

    /** @var float|null Tax percentage. */
    private ?float $percent = null;

    /** @var TaxScheme|null Tax scheme information. */
    private ?TaxScheme $taxScheme = null;

    /** @var string|null Tax exemption reason. */
    private ?string $taxExemptionReason = null;

    /** @var string|null Tax exemption reason code. */
    private ?string $taxExemptionReasonCode = null;

    /** @var string|null Scheme ID attribute for the tax category. */
    private ?string $schemeID = null;

    /** @var string|null Scheme name attribute for the tax category. */
    private ?string $schemeName = null;

    public const UNCL5305 = 'UNCL5305';

    /**
     * Get the tax category identifier.
     *
     * If not set, it is derived from the percent value.
     *
     * @return string|null
     */
    public function getId(): ?string
    {
        if (!empty($this->id)) {
            return $this->id;
        }

        if ($this->getPercent() !== null) {
            if ($this->getPercent() >= 15) {
                return 'S';
            } elseif ($this->getPercent() >= 6 && $this->getPercent() < 15) {
                return 'AA';
            } else {
                return 'Z';
            }
        }

        return null;
    }

    /**
     * Set the tax category identifier.
     *
     * @param string|null $id
     * @return self
     * @throws InvalidArgumentException if the provided ID is an empty string.
     */
    public function setId(?string $id): self
    {
        if ($id !== null && trim($id) === '') {
            throw new InvalidArgumentException('Tax category ID cannot be empty.');
        }
        $this->id = $id;
        return $this;
    }

    /**
     * Get the tax category name.
     *
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Set the tax category name.
     *
     * @param string|null $name
     * @return self
     */
    public function setName(?string $name): self
    {
        if ($name !== null && trim($name) === '') {
            throw new InvalidArgumentException('Tax category name cannot be empty.');
        }
        $this->name = $name;
        return $this;
    }

    /**
     * Get the tax percentage.
     *
     * @return float|null
     */
    public function getPercent(): ?float
    {
        return $this->percent;
    }

    /**
     * Set the tax percentage.
     *
     * @param float|null $percent
     * @return self
     * @throws InvalidArgumentException if the percentage is negative.
     */
    public function setPercent(?float $percent): self
    {
        if ($percent !== null && $percent < 0) {
            throw new InvalidArgumentException('Tax percent must be non-negative.');
        }
        $this->percent = $percent;
        return $this;
    }

    /**
     * Get the tax scheme.
     *
     * @return TaxScheme|null
     */
    public function getTaxScheme(): ?TaxScheme
    {
        return $this->taxScheme;
    }

    /**
     * Set the tax scheme.
     *
     * @param TaxScheme|null $taxScheme
     * @return self
     */
    public function setTaxScheme(?TaxScheme $taxScheme): self
    {
        $this->taxScheme = $taxScheme;
        return $this;
    }

    /**
     * Get the scheme ID.
     *
     * @return string|null
     */
    public function getSchemeID(): ?string
    {
        return $this->schemeID;
    }

    /**
     * Set the scheme ID.
     *
     * @param string|null $schemeID
     * @return self
     */
    public function setSchemeID(?string $schemeID): self
    {
        if ($schemeID !== null && trim($schemeID) === '') {
            throw new InvalidArgumentException('Scheme ID cannot be empty.');
        }
        $this->schemeID = $schemeID;
        return $this;
    }

    /**
     * Get the scheme name.
     *
     * @return string|null
     */
    public function getSchemeName(): ?string
    {
        return $this->schemeName;
    }

    /**
     * Set the scheme name.
     *
     * @param string|null $schemeName
     * @return self
     */
    public function setSchemeName(?string $schemeName): self
    {
        if ($schemeName !== null && trim($schemeName) === '') {
            throw new InvalidArgumentException('Scheme name cannot be empty.');
        }
        $this->schemeName = $schemeName;
        return $this;
    }

    /**
     * Get the tax exemption reason.
     *
     * @return string|null
     */
    public function getTaxExemptionReason(): ?string
    {
        return $this->taxExemptionReason;
    }

    /**
     * Set the tax exemption reason.
     *
     * @param string|null $taxExemptionReason
     * @return self
     */
    public function setTaxExemptionReason(?string $taxExemptionReason): self
    {
        if ($taxExemptionReason !== null && trim($taxExemptionReason) === '') {
            throw new InvalidArgumentException('Tax exemption reason cannot be empty.');
        }
        $this->taxExemptionReason = $taxExemptionReason;
        return $this;
    }

    /**
     * Get the tax exemption reason code.
     *
     * @return string|null
     */
    public function getTaxExemptionReasonCode(): ?string
    {
        return $this->taxExemptionReasonCode;
    }

    /**
     * Set the tax exemption reason code.
     *
     * @param string|null $taxExemptionReasonCode
     * @return self
     */
    public function setTaxExemptionReasonCode(?string $taxExemptionReasonCode): self
    {
        if ($taxExemptionReasonCode !== null && trim($taxExemptionReasonCode) === '') {
            throw new InvalidArgumentException('Tax exemption reason code cannot be empty.');
        }
        $this->taxExemptionReasonCode = $taxExemptionReasonCode;
        return $this;
    }

    /**
     * Validates required data before XML serialization.
     *
     * @return void
     * @throws InvalidArgumentException if required data is missing.
     */
    public function validate(): void
    {
        if ($this->getId() === null) {
            throw new InvalidArgumentException('Missing tax category ID.');
        }

        if ($this->getPercent() === null) {
            throw new InvalidArgumentException('Missing tax category percent.');
        }
    }

    /**
     * Serializes this object to XML.
     *
     * @param Writer $writer
     * @return void
     */
    public function xmlSerialize(Writer $writer): void
    {
        $this->validate();

        $schemeAttributes = [];
        if ($this->schemeID !== null) {
            $schemeAttributes['schemeID'] = $this->schemeID;
        }
        if ($this->schemeName !== null) {
            $schemeAttributes['schemeName'] = $this->schemeName;
        }

        $writer->write([
            [
                'name'       => Schema::CBC . 'ID',
                'value'      => $this->getId(),
                'attributes' => $schemeAttributes,
            ],
            Schema::CBC . 'Percent' => number_format($this->percent, 2, '.', ''),
        ]);

        if ($this->name !== null) {
            $writer->write([
                Schema::CBC . 'Name' => $this->name,
            ]);
        }

        if ($this->taxExemptionReasonCode !== null) {
            $writer->write([
                Schema::CBC . 'TaxExemptionReasonCode' => $this->taxExemptionReasonCode,
                Schema::CBC . 'TaxExemptionReason'     => $this->taxExemptionReason,
            ]);
        }

        if ($this->taxScheme !== null) {
            $writer->write([
                Schema::CAC . 'TaxScheme' => $this->taxScheme,
            ]);
        }
    }
}
