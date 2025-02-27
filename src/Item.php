<?php
namespace Saleh7\Zatca;

use InvalidArgumentException;
use Sabre\Xml\Writer;
use Sabre\Xml\XmlSerializable;

/**
 * Class Item
 *
 * Represents an item in an invoice with its details for XML serialization.
 */
class Item implements XmlSerializable
{
    /** @var string|null Description of the item. */
    private ?string $description = null;

    /** @var string|null Name of the item (mandatory). */
    private ?string $name = null;

    /** @var string|null Standard item identification. */
    private ?string $standardItemIdentification = null;

    /** @var string|null Buyers item identification. */
    private ?string $buyersItemIdentification = null;

    /** @var string|null Sellers item identification. */
    private ?string $sellersItemIdentification = null;

    /** @var array|null Classified tax category. */
    private ?array $classifiedTaxCategory = [];

    /**
     * Set the item description.
     *
     * @param string|null $description
     * @return self
     * @throws InvalidArgumentException if provided description is an empty string.
     */
    public function setDescription(?string $description): self
    {
        if ($description !== null && trim($description) === '') {
            throw new InvalidArgumentException('Description cannot be an empty string.');
        }
        $this->description = $description;
        return $this;
    }

    /**
     * Get the item description.
     *
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * Set the item name.
     *
     * @param string|null $name
     * @return self
     * @throws InvalidArgumentException if provided name is empty.
     */
    public function setName(?string $name): self
    {
        if ($name !== null && trim($name) === '') {
            throw new InvalidArgumentException('Name cannot be empty.');
        }
        $this->name = $name;
        return $this;
    }

    /**
     * Get the item name.
     *
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Set the standard item identification.
     *
     * @param string|null $standardItemIdentification
     * @return self
     */
    public function setStandardItemIdentification(?string $standardItemIdentification): self
    {
        $this->standardItemIdentification = $standardItemIdentification;
        return $this;
    }

    /**
     * Get the standard item identification.
     *
     * @return string|null
     */
    public function getStandardItemIdentification(): ?string
    {
        return $this->standardItemIdentification;
    }

    /**
     * Set the buyers item identification.
     *
     * @param string|null $buyersItemIdentification
     * @return self
     * @throws InvalidArgumentException if provided value is empty.
     */
    public function setBuyersItemIdentification(?string $buyersItemIdentification): self
    {
        if ($buyersItemIdentification !== null && trim($buyersItemIdentification) === '') {
            throw new InvalidArgumentException('Buyers item identification cannot be empty.');
        }
        $this->buyersItemIdentification = $buyersItemIdentification;
        return $this;
    }

    /**
     * Get the buyers item identification.
     *
     * @return string|null
     */
    public function getBuyersItemIdentification(): ?string
    {
        return $this->buyersItemIdentification;
    }

    /**
     * Set the sellers item identification.
     *
     * @param string|null $sellersItemIdentification
     * @return self
     * @throws InvalidArgumentException if provided value is empty.
     */
    public function setSellersItemIdentification(?string $sellersItemIdentification): self
    {
        if ($sellersItemIdentification !== null && trim($sellersItemIdentification) === '') {
            throw new InvalidArgumentException('Sellers item identification cannot be empty.');
        }
        $this->sellersItemIdentification = $sellersItemIdentification;
        return $this;
    }

    /**
     * Get the sellers item identification.
     *
     * @return string|null
     */
    public function getSellersItemIdentification(): ?string
    {
        return $this->sellersItemIdentification;
    }

    /**
     * Set the classified tax category.
     *
     * @param array|null $classifiedTaxCategory
     * @return self
     */
    public function setClassifiedTaxCategory(?array $classifiedTaxCategory): self
    {
        $this->classifiedTaxCategory = $classifiedTaxCategory;
        return $this;
    }

    /**
     * Get the classified tax category.
     *
     * @return array|null
     */
    public function getClassifiedTaxCategory(): ?array
    {
        return $this->classifiedTaxCategory;
    }

    /**
     * Serializes this object to XML.
     *
     * @param Writer $writer The XML writer.
     * @return void
     */
    public function xmlSerialize(Writer $writer): void
    {
        // Write mandatory Name element.
        $writer->write([
            Schema::CBC . 'Name' => $this->name,
        ]);

        // Write Description element if provided.
        if ($this->description !== null) {
            $writer->write([
                Schema::CBC . 'Description' => $this->description,
            ]);
        }

        // Write StandardItemIdentification element if provided.
        if (!empty($this->getStandardItemIdentification())) {
            $writer->write([
                Schema::CAC . 'StandardItemIdentification' => [
                    Schema::CBC . 'ID' => $this->standardItemIdentification,
                ],
            ]);
        }

        // Write BuyersItemIdentification element if provided.
        if (!empty($this->buyersItemIdentification)) {
            $writer->write([
                Schema::CAC . 'BuyersItemIdentification' => [
                    Schema::CBC . 'ID' => $this->buyersItemIdentification,
                ],
            ]);
        }

        // Write SellersItemIdentification element if provided.
        if (!empty($this->sellersItemIdentification)) {
            $writer->write([
                Schema::CAC . 'SellersItemIdentification' => [
                    Schema::CBC . 'ID' => $this->sellersItemIdentification,
                ],
            ]);
        }

        // Write ClassifiedTaxCategory element if provided.
        if (!empty($this->classifiedTaxCategory)) {
            foreach($this->classifiedTaxCategory as $classifiedTaxCategory){
                $writer->write([
                    Schema::CAC . 'ClassifiedTaxCategory' => $classifiedTaxCategory
                ]);
            }
        }
    }
}
