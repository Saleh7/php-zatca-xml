<?php
namespace Saleh7\Zatca;

use InvalidArgumentException;
use Sabre\Xml\Writer;
use Sabre\Xml\XmlSerializable;

/**
 * Class UBLExtensions
 *
 * Represents a collection of UBL extensions for XML serialization.
 */
class UBLExtensions implements XmlSerializable
{
    /** @var UBLExtension[] Array of UBLExtension objects. */
    private array $UBLExtensions = [];

    /**
     * Get the UBL extensions.
     *
     * @return UBLExtension[]
     */
    public function getUBLExtensions(): array
    {
        return $this->UBLExtensions;
    }

    /**
     * Set the UBL extensions.
     *
     * @param UBLExtension[] $UBLExtensions
     * @return self
     */
    public function setUBLExtensions(array $UBLExtensions): self
    {
        $this->UBLExtensions = $UBLExtensions;
        return $this;
    }

    /**
     * Validates that UBLExtensions are set.
     *
     * @return void
     * @throws InvalidArgumentException if UBLExtensions are not provided.
     */
    private function validate(): void
    {
        if (empty($this->UBLExtensions)) {
            throw new InvalidArgumentException("Missing UBL Extension(s).");
        }
    }

    /**
     * Serializes this object to XML.
     *
     * @param Writer $writer The XML writer.
     * @return void
     */
    public function xmlSerialize(Writer $writer): void
    {
        $this->validate();
        foreach ($this->UBLExtensions as $UBLExtension) {
            $writer->write([
                Schema::EXT . 'UBLExtension' => $UBLExtension
            ]);
        }
    }
}
