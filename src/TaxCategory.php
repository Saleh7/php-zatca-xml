<?php
namespace Saleh7\Zatca;

use Sabre\Xml\Writer;
use Sabre\Xml\XmlSerializable;
use InvalidArgumentException;

class TaxCategory implements XmlSerializable
{
    private $id;
    private $idAttributes = [
        'schemeID' => TaxCategory::UNCL5305,
        'schemeAgencyID' => '6'
    ];
    private $name;
    private $percent;
    private $taxScheme;
    private $taxSchemeAttributes = [
        'schemeID' => TaxCategory::UNCL5153,
        'schemeAgencyID' => '6'
    ];
    private $taxExemptionReason;
    private $taxExemptionReasonCode;

    public const UNCL5305 = 'UN/ECE 5305';
    public const UNCL5153 = 'UN/ECE 5153';

    /**
     * @return string
     */
    public function getId(): ?string
    {
        if (!empty($this->id)) {
            return $this->id;
        }

        if ($this->getPercent() !== null) {
            if ($this->getPercent() >= 15) {
                return 'S';
            } elseif ($this->getPercent() <= 15 && $this->getPercent() >= 6) {
                return 'AA';
            } else {
                return 'Z';
            }
        }

        return null;
    }

    /**
     * @param string $id
     * @param array $attributes
     * @return TaxCategory
     */
    public function setId(?string $id, $attributes = null): TaxCategory
    {
        $this->id = $id;
        if (isset($attributes)) {
            $this->idAttributes = $attributes;
        }
        return $this;
    }

    /**
     * @param string $name
     * @return TaxCategory
     */
    public function setName(?string $name): TaxCategory
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getPercent(): ?float
    {
        return $this->percent;
    }

    /**
     * @param string $percent
     * @return TaxCategory
     */
    public function setPercent(?float $percent): TaxCategory
    {
        $this->percent = $percent;
        return $this;
    }

    /**
     * @return string
     */
    public function getTaxScheme(): ?TaxScheme
    {
        return $this->taxScheme;
    }

    /**
     * @param TaxScheme $taxScheme
     * @param array $attributes
     * @return TaxCategory
     */
    public function setTaxScheme(?TaxScheme $taxScheme, $attributes = null): TaxCategory
    {
        $this->taxScheme = $taxScheme;
        if (isset($attributes)) {
            $this->taxSchemeAttributes = $attributes;
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getTaxExemptionReason(): ?string
    {
        return $this->taxExemptionReason;
    }

    /**
     * @param string $taxExemptionReason
     * @return TaxCategory
     */
    public function setTaxExemptionReason(?string $taxExemptionReason): TaxCategory
    {
        $this->taxExemptionReason = $taxExemptionReason;
        return $this;
    }

    /**
     * @param string $taxExemptionReason
     * @return TaxCategory
     */
    public function setTaxExemptionReasonCode(?string $taxExemptionReasonCode): TaxCategory
    {
        $this->taxExemptionReasonCode = $taxExemptionReasonCode;
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
        if ($this->getId() === null) {
            throw new InvalidArgumentException('Missing taxcategory id');
        }

        if ($this->getPercent() === null) {
            throw new InvalidArgumentException('Missing taxcategory percent');
        }
    }

    /**
     * The xmlSerialize method is called during xml writing.
     *
     * @param Writer $writer
     * @return void
     */
    public function xmlSerialize(Writer $writer): void
    {
        $this->validate();

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
                    'value' => [ Schema::CBC . 'ID' => [
                        "value" => $this->getTaxScheme()->id,
                        'attributes' => $this->taxSchemeAttributes
                    ]
                ]]
            ]);

        } else {
            $writer->write([
                Schema::CAC . 'TaxScheme' => null,
            ]);
        }
    }
}
