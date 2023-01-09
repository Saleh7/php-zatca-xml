<?php
namespace Saleh7\Zatca;

use Sabre\Xml\Writer;
use Sabre\Xml\XmlSerializable;
use InvalidArgumentException;

class PartyTaxScheme implements XmlSerializable
{
    private $companyId;
    private $taxScheme;

    /**
     * @param string $companyId
     * @return PartyTaxScheme
     */
    public function setCompanyId($companyId): PartyTaxScheme
    {
        $this->companyId = $companyId;
        return $this;
    }

    /**
     * @param TaxScheme $taxScheme
     * @return PartyTaxScheme
     */
    public function setTaxScheme(TaxScheme $taxScheme): PartyTaxScheme
    {
        $this->taxScheme = $taxScheme;
        return $this;
    }

    /**
     * The validate function that is called during xml writing to valid the data of the object.
     *
     * @return void
     * @throws InvalidArgumentException An error with information about required data that is missing to write the XML
     */
    public function validate()
    {
        if ($this->taxScheme === null) {
            throw new InvalidArgumentException('Missing TaxScheme');
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
        if ($this->companyId !== null) {
            $writer->write([
                Schema::CBC . 'CompanyID' => $this->companyId
            ]);
        }
        if ($this->taxScheme !== null) {
            $writer->write([
                Schema::CAC . 'TaxScheme' => $this->taxScheme
            ]);
        }
    }
}
