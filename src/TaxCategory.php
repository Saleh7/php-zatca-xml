<?php
namespace Saleh7\Zatca;

use Sabre\Xml\Writer;
use Sabre\Xml\XmlSerializable;
use InvalidArgumentException;

/**
 * Class TaxCategory
 *
 * Represents a tax category for an invoice with XML serialization.
 */
class TaxCategory implements XmlSerializable
{
    /** @var string|null Tax category identifier. */
    private ?string $id = null;

    /** @var array Attributes for the ID element. */
    private array $idAttributes = [
        'schemeID' => self::UNCL5305,
        'schemeAgencyID' => '6'
    ];

    /** @var string|null Tax category name. */
    private ?string $name = null;

    /** @var float|null Tax percentage. */
    private ?float $percent = null;

    /** @var TaxScheme|null Tax scheme object. */
    private ?TaxScheme $taxScheme = null;

    /** @var array Attributes for the TaxScheme element. */
    private array $taxSchemeAttributes = [
        'schemeID' => self::UNCL5153,
        'schemeAgencyID' => '6'
    ];

    /** @var string|null Tax exemption reason. */
    private ?string $taxExemptionReason = null;

    /** @var string|null Tax exemption reason code. */
    private ?string $taxExemptionReasonCode = null;

    public const UNCL5305 = 'UN/ECE 5305';
    public const UNCL5153 = 'UN/ECE 5153';

    /**
     * Get the tax category identifier.
     *
     * If not explicitly set, it is derived from the percent value.
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
            } elseif ($this->getPercent() >= 6) {
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
     * @param array|null $attributes Optional attributes to override default ID attributes.
     * @return self
     */
    public function setId(?string $id, ?array $attributes = null): self
    {
        $this->id = $id;
        if ($attributes !== null) {
            $this->idAttributes = $attributes;
        }
        return $this;
    }

    /**
     * Set the tax category name.
     *
     * @param string|null $name
     * @return self
     */
    public function setName(?string $name): self
    {
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
     */
    public function setPercent(?float $percent): self
    {
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
     * @param array|null $attributes Optional attributes to override default TaxScheme attributes.
     * @return self
     */
    public function setTaxScheme(?TaxScheme $taxScheme, ?array $attributes = null): self
    {
        $this->taxScheme = $taxScheme;
        if ($attributes !== null) {
            $this->taxSchemeAttributes = $attributes;
        }
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
        $this->taxExemptionReason = $taxExemptionReason;
        return $this;
    }

    /**
     * Set the tax exemption reason code.
     *
     * @param string|null $taxExemptionReasonCode
     * @return self
     */
    public function setTaxExemptionReasonCode(?string $taxExemptionReasonCode): self
    {
        $this->taxExemptionReasonCode = $taxExemptionReasonCode;
        return $this;
    }

    /**
     * Validate required fields before serialization.
     *
     * @return void
     * @throws InvalidArgumentException if required fields are missing.
     */
    public function validate(): void
    {
        if ($this->getId() === null) {
            throw new InvalidArgumentException('Missing tax category id.');
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

        // Write ID element with attributes
        $writer->write([
            [
                'name' => Schema::CBC . 'ID',
                'value' => $this->getId(),
                'attributes' => $this->idAttributes,
            ],
        ]);

        if ($this->name !== null) {
            $writer->write([
                Schema::CBC . 'Name' => $this->name,
            ]);
        }

        // Write percent without decimals
        $writer->write([
            Schema::CBC . 'Percent' => number_format($this->percent, 0, '.', ''),
        ]);

        if ($this->taxExemptionReasonCode !== null) {
            $writer->write([
                Schema::CBC . 'TaxExemptionReasonCode' => $this->taxExemptionReasonCode,
            ]);
        }

        if ($this->taxExemptionReason !== null) {
            $writer->write([
                Schema::CBC . 'TaxExemptionReason' => $this->taxExemptionReason,
            ]);
        }

        if ($this->taxScheme !== null) {
            $writer->write([
                [
                    'name' => Schema::CAC . 'TaxScheme',
                    'value' => [
                        Schema::CBC . 'ID' => [
                            "value" => $this->taxScheme->getId(), // Use public getter here.
                            'attributes' => $this->taxSchemeAttributes
                        ]
                    ]
                ]
            ]);
        } else {
            $writer->write([
                Schema::CAC . 'TaxScheme' => null,
            ]);
        }
    }
}
