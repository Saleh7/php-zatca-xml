<?php
namespace Saleh7\Zatca;

use Sabre\Xml\Writer;
use Sabre\Xml\XmlSerializable;
use InvalidArgumentException;

/**
 * Class SignatureInformation
 *
 * Represents signature information used in digital signatures for XML serialization.
 */
class SignatureInformation implements XmlSerializable
{
    /** @var string Signature identifier. */
    private string $id;

    /** @var string Referenced signature identifier. */
    private string $referencedSignatureID;

    /**
     * Set the signature identifier.
     *
     * @param string $id
     * @return self
     * @throws InvalidArgumentException if $id is empty.
     */
    public function setID(string $id): self
    {
        if (trim($id) === '') {
            throw new InvalidArgumentException('Signature ID cannot be empty.');
        }
        $this->id = $id;
        return $this;
    }

    /**
     * Set the referenced signature identifier.
     *
     * @param string $referencedSignatureID
     * @return self
     * @throws InvalidArgumentException if $referencedSignatureID is empty.
     */
    public function setReferencedSignatureID(string $referencedSignatureID): self
    {
        if (trim($referencedSignatureID) === '') {
            throw new InvalidArgumentException('Referenced Signature ID cannot be empty.');
        }
        $this->referencedSignatureID = $referencedSignatureID;
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
        $writer->write([
            [ Schema::CBC . 'ID' => $this->id ],
            [ Schema::SBC . 'ReferencedSignatureID' => $this->referencedSignatureID ]
        ]);
    }
}
