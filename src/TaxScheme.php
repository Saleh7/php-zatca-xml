<?php
namespace Saleh7\Zatca;

use Sabre\Xml\Writer;
use Sabre\Xml\XmlSerializable;

class TaxScheme implements XmlSerializable
{
    public $id;
    private $taxTypeCode;
    private $name;

    /**
     * @param string $id
     * @return TaxScheme
     */
    public function setId(string $id): TaxScheme
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @param string $taxTypeCode
     * @return TaxScheme
     */
    public function setTaxTypeCode(?string $taxTypeCode)
    {
        $this->taxTypeCode = $taxTypeCode;
        return $this;
    }

    /**
     * @param string $name
     * @return TaxScheme
     */
    public function setName(?string $name)
    {
        $this->name = $name;
        return $this;
    }

    public function xmlSerialize(Writer $writer): void
    {

        if ($this->id !== null) {
            $writer->write( [ Schema::CBC . 'ID' => $this->id ] );
        }
        if ($this->taxTypeCode !== null) {
            $writer->write([
                Schema::CBC . 'TaxTypeCode' => $this->taxTypeCode
            ]);
        }
        if ($this->name !== null) {
            $writer->write([
                Schema::CBC . 'Name' => $this->name
            ]);
        }
    }
}
