<?php
namespace Saleh7\Zatca;

use InvalidArgumentException;
use Sabre\Xml\Writer;
use Sabre\Xml\XmlSerializable;

/**
 * Class BillingReference
 *
 * Represents a billing reference for an invoice.
 */
class BillingReference implements XmlSerializable
{
    /** @var string|null Identifier for the billing reference. */
    private ?string $id = null;

    /**
     * Set the billing reference identifier.
     *
     * @param string $id Identifier must not be empty.
     * @return self
     * @throws InvalidArgumentException if the ID is empty.
     */
    public function setId(string $id): self
    {
        if (trim($id) === '') {
            throw new InvalidArgumentException('ID cannot be empty.');
        }
        $this->id = $id;
        return $this;
    }

    /**
     * Get the billing reference identifier.
     *
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * Serializes this object to XML.
     *
     * @param Writer $writer The XML writer.
     * @return void
     */
    public function xmlSerialize(Writer $writer): void
    {
        if ($this->id !== null) {
            $writer->write([
                Schema::CAC . 'InvoiceDocumentReference' => [
                    Schema::CBC . 'ID' => $this->id
                ]
            ]);
        }
    }
}
