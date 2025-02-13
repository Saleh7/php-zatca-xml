<?php
namespace Saleh7\Zatca;

use InvalidArgumentException;
use Sabre\Xml\Writer;
use Sabre\Xml\XmlSerializable;

/**
 * Class PartyTaxScheme
 *
 * Represents a party's tax scheme information for XML serialization.
 */
class PartyTaxScheme implements XmlSerializable
{
    /** @var string|null Company identification. */
    private ?string $companyId = null;

    /** @var TaxScheme|null Tax scheme details. */
    private ?TaxScheme $taxScheme = null;

    /**
     * Set the company ID.
     *
     * @param string|null $companyId
     * @return self
     * @throws InvalidArgumentException if company ID is empty.
     */
    public function setCompanyId(?string $companyId): self
    {
        if ($companyId !== null && trim($companyId) === '') {
            throw new InvalidArgumentException('Company ID cannot be empty.');
        }
        $this->companyId = $companyId;
        return $this;
    }

    /**
     * Set the tax scheme.
     *
     * @param TaxScheme $taxScheme
     * @return self
     */
    public function setTaxScheme(TaxScheme $taxScheme): self
    {
        $this->taxScheme = $taxScheme;
        return $this;
    }

    /**
     * Validate that the required data is set.
     *
     * @return void
     * @throws InvalidArgumentException if the tax scheme is missing.
     */
    public function validate(): void
    {
        if ($this->taxScheme === null) {
            throw new InvalidArgumentException('Missing TaxScheme.');
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

        if ($this->companyId !== null) {
            $writer->write([
                Schema::CBC . 'CompanyID' => $this->companyId,
            ]);
        }
        if ($this->taxScheme !== null) {
            $writer->write([
                Schema::CAC . 'TaxScheme' => $this->taxScheme,
            ]);
        }
    }
}
