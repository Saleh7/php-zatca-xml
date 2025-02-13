<?php
namespace Saleh7\Zatca;

use InvalidArgumentException;
use Sabre\Xml\Writer;
use Sabre\Xml\XmlSerializable;

/**
 * Class Contract
 *
 * Represents a contract with an identifier for XML serialization.
 */
class Contract implements XmlSerializable
{
    /** @var string|null Contract identifier. */
    private ?string $id = null;

    /**
     * Get the contract identifier.
     *
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * Set the contract identifier.
     *
     * @param string $id
     * @return self
     * @throws InvalidArgumentException if the id is empty.
     */
    public function setId(string $id): self
    {
        if (trim($id) === '') {
            throw new InvalidArgumentException('Contract ID cannot be empty.');
        }
        $this->id = $id;
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
        if ($this->id !== null) {
            $writer->write([
                Schema::CBC . 'ID' => $this->id,
            ]);
        }
    }
}
