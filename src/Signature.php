<?php
namespace Saleh7\Zatca;

use Sabre\Xml\Writer;
use Sabre\Xml\XmlSerializable;
use InvalidArgumentException;

/**
 * Class Signature
 *
 * Represents signature information for XML serialization.
 */
class Signature implements XmlSerializable
{
    /** @var string Signature identifier. */
    private string $id = "urn:oasis:names:specification:ubl:signature:Invoice";

    /** @var string Signature method. */
    private string $signatureMethod = "urn:oasis:names:specification:ubl:dsig:enveloped:xades";

    /**
     * Set the signature identifier.
     *
     * @param string $id
     * @return self
     * @throws InvalidArgumentException if the provided ID is empty.
     */
    public function setId(string $id): self
    {
        if (trim($id) === '') {
            throw new InvalidArgumentException('Signature ID cannot be empty.');
        }
        $this->id = $id;
        return $this;
    }

    /**
     * Set the signature method.
     *
     * @param string $method
     * @return self
     * @throws InvalidArgumentException if the provided method is empty.
     */
    public function setSignatureMethod(string $method): self
    {
        if (trim($method) === '') {
            throw new InvalidArgumentException('Signature method cannot be empty.');
        }
        $this->signatureMethod = $method;
        return $this;
    }

    /**
     * Serializes this object to XML.
     *
     * @param Writer $writer The XML writer instance.
     * @return void
     */
    public function xmlSerialize(Writer $writer): void
    {
        $writer->write([
            Schema::CBC . "ID" => $this->id,
            Schema::CBC . "SignatureMethod" => $this->signatureMethod,
        ]);
    }
}
