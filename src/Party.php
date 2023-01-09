<?php
namespace Saleh7\Zatca;

use Sabre\Xml\Writer;
use Sabre\Xml\XmlSerializable;

class Party implements XmlSerializable
{
    private $partyIdentification;
    private $partyIdentificationId;
    private $postalAddress;
    private $partyTaxScheme;
    private $legalEntity;

    /**
     * @param string $partyIdentification
     * @return Party
     */
    public function setPartyIdentification(?string $partyIdentification): Party
    {
        $this->partyIdentification = $partyIdentification;
        return $this;
    }

    /**
     * @param string $partyIdentificationId
     * @return Party
     */
    public function setPartyIdentificationId(?string $partyIdentificationId): Party
    {
        $this->partyIdentificationId = $partyIdentificationId;
        return $this;
    }

    /**
     * @param Address $postalAddress
     * @return Party
     */
    public function setPostalAddress(?Address $postalAddress): Party
    {
        $this->postalAddress = $postalAddress;
        return $this;
    }

    /**
     * @param PartyTaxScheme $partyTaxScheme
     * @return Party
     */
    public function setPartyTaxScheme(PartyTaxScheme $partyTaxScheme)
    {
        $this->partyTaxScheme = $partyTaxScheme;
        return $this;
    }

    /**
     * @param LegalEntity $legalEntity
     * @return Party
     */
    public function setLegalEntity(?LegalEntity $legalEntity): Party
    {
        $this->legalEntity = $legalEntity;
        return $this;
    }

    /**
     * The xmlSerialize method is called during xml writing.
     *
     * @param Writer $writer
     * @return void
     */
    public function xmlSerialize(Writer $writer): void
    {
        
        if ($this->partyIdentification !== null) {
            $writer->write([
                'name' => Schema::CAC . 'PartyIdentification',
                'value' => [
                    "name" => Schema::CBC . 'ID',
                    "value" => $this->partyIdentification,
                    "attributes" => [
                        "schemeID" => "$this->partyIdentificationId"
                    ]
                ]
            ]);
        }
         // PostalAddress
        if ($this->postalAddress !== null) {
            $writer->write( [ Schema::CAC . 'PostalAddress' => $this->postalAddress ] );
        }
         //partyTaxScheme
        if ($this->partyTaxScheme !== null) {
            $writer->write( [ Schema::CAC . 'PartyTaxScheme' => $this->partyTaxScheme ] );
        }
         // PartyLegalEntity
        if ($this->legalEntity !== null) {
            $writer->write( [ Schema::CAC . 'PartyLegalEntity' => $this->legalEntity ] );
        }
    }
}
