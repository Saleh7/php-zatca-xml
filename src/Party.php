<?php
namespace Saleh7\Zatca;

use Sabre\Xml\Writer;
use Sabre\Xml\XmlSerializable;
use InvalidArgumentException;

/**
 * Class Party
 *
 * Represents a party with identification, address, tax scheme, and legal entity information for XML serialization.
 */
class Party implements XmlSerializable
{
    /** @var string|null Party identification value. */
    private ?string $partyIdentification = null;

    /** @var string|null Party identification scheme identifier. */
    private ?string $partyIdentificationId = null;

    /** @var Address|null Postal address. */
    private ?Address $postalAddress = null;

    /** @var PartyTaxScheme|null Party tax scheme details. */
    private ?PartyTaxScheme $partyTaxScheme = null;

    /** @var LegalEntity|null Legal entity details. */
    private ?LegalEntity $legalEntity = null;

    /**
     * Set the party identification.
     *
     * @param string|null $partyIdentification
     * @return self
     * @throws InvalidArgumentException if an empty string is provided.
     */
    public function setPartyIdentification(?string $partyIdentification): self
    {
        if ($partyIdentification !== null && trim($partyIdentification) === '') {
            throw new InvalidArgumentException('Party identification cannot be empty.');
        }
        $this->partyIdentification = $partyIdentification;
        return $this;
    }

    /**
     * Set the party identification scheme identifier.
     *
     * @param string|null $partyIdentificationId
     * @return self
     */
    public function setPartyIdentificationId(?string $partyIdentificationId): self
    {
        if ($partyIdentificationId !== null && trim($partyIdentificationId) === '') {
            throw new InvalidArgumentException('Party identification scheme ID cannot be empty.');
        }
        $this->partyIdentificationId = $partyIdentificationId;
        return $this;
    }

    /**
     * Set the postal address.
     *
     * @param Address|null $postalAddress
     * @return self
     */
    public function setPostalAddress(?Address $postalAddress): self
    {
        $this->postalAddress = $postalAddress;
        return $this;
    }

    /**
     * Set the party tax scheme.
     *
     * @param PartyTaxScheme $partyTaxScheme
     * @return self
     */
    public function setPartyTaxScheme(PartyTaxScheme $partyTaxScheme): self
    {
        $this->partyTaxScheme = $partyTaxScheme;
        return $this;
    }

    /**
     * Set the legal entity.
     *
     * @param LegalEntity|null $legalEntity
     * @return self
     */
    public function setLegalEntity(?LegalEntity $legalEntity): self
    {
        $this->legalEntity = $legalEntity;
        return $this;
    }

    /**
     * Serializes this object to XML.
     *
     * @param Writer $writer
     * @return void
     */
    public function xmlSerialize(Writer $writer): void
    {
        // PartyIdentification element with schemeID attribute
        if ($this->partyIdentification !== null) {
            $writer->write([
                'name'  => Schema::CAC . 'PartyIdentification',
                'value' => [
                    'name'       => Schema::CBC . 'ID',
                    'value'      => $this->partyIdentification,
                    'attributes' => [
                        'schemeID' => $this->partyIdentificationId ?? ''
                    ]
                ]
            ]);
        }

        // PostalAddress element
        if ($this->postalAddress !== null) {
            $writer->write([
                Schema::CAC . 'PostalAddress' => $this->postalAddress
            ]);
        }

        // PartyTaxScheme element
        if ($this->partyTaxScheme !== null) {
            $writer->write([
                Schema::CAC . 'PartyTaxScheme' => $this->partyTaxScheme
            ]);
        }

        // PartyLegalEntity element
        if ($this->legalEntity !== null) {
            $writer->write([
                Schema::CAC . 'PartyLegalEntity' => $this->legalEntity
            ]);
        }
    }
}
