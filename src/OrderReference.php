<?php
namespace Saleh7\Zatca;

use Sabre\Xml\Writer;
use Sabre\Xml\XmlSerializable;
use InvalidArgumentException;

/**
 * Class OrderReference
 *
 * Represents an order reference for an invoice and provides XML serialization.
 */
class OrderReference implements XmlSerializable
{
    /** @var string|null Order reference identifier. */
    private ?string $id = null;

    /** @var string|null Sales order identifier. */
    private ?string $salesOrderId = null;

    /**
     * Set the order reference identifier.
     *
     * @param string $id
     * @return self
     * @throws InvalidArgumentException if the provided ID is empty.
     */
    public function setId(string $id): self
    {
        if (trim($id) === '') {
            throw new InvalidArgumentException('Order reference ID cannot be empty.');
        }
        $this->id = $id;
        return $this;
    }

    /**
     * Set the sales order identifier.
     *
     * @param string $salesOrderId
     * @return self
     * @throws InvalidArgumentException if the provided SalesOrderID is empty.
     */
    public function setSalesOrderId(string $salesOrderId): self
    {
        if (trim($salesOrderId) === '') {
            throw new InvalidArgumentException('Sales order ID cannot be empty.');
        }
        $this->salesOrderId = $salesOrderId;
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
                Schema::CBC . 'ID' => $this->id
            ]);
        }
        if ($this->salesOrderId !== null) {
            $writer->write([
                Schema::CBC . 'SalesOrderID' => $this->salesOrderId
            ]);
        }
    }
}
