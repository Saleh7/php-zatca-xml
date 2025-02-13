<?php
namespace Saleh7\Zatca;

use Sabre\Xml\Writer;
use Sabre\Xml\XmlSerializable;
use InvalidArgumentException;

/**
 * Class UBLExtension
 *
 * Represents an individual UBL extension for XML serialization.
 */
class UBLExtension implements XmlSerializable
{
    /** @var string|null The extension URI. */
    private ?string $extensionURI = null;
    
    /** @var ExtensionContent|null The extension content. */
    private ?ExtensionContent $extensionContent = null;

    /**
     * Get the extension URI.
     *
     * @return string|null
     */
    public function getExtensionURI(): ?string
    {
        return $this->extensionURI;
    }

    /**
     * Set the extension URI.
     *
     * @param string $uri
     * @return self
     * @throws InvalidArgumentException if $uri is empty.
     */
    public function setExtensionURI(string $uri): self
    {
        if (trim($uri) === '') {
            throw new InvalidArgumentException('Extension URI cannot be empty.');
        }
        $this->extensionURI = $uri;
        return $this;
    }

    /**
     * Get the extension content.
     *
     * @return ExtensionContent|null
     */
    public function getExtensionContent(): ?ExtensionContent
    {
        return $this->extensionContent;
    }

    /**
     * Set the extension content.
     *
     * @param ExtensionContent $extensionContent
     * @return self
     */
    public function setExtensionContent(ExtensionContent $extensionContent): self
    {
        $this->extensionContent = $extensionContent;
        return $this;
    }

    /**
     * Serializes this object to XML.
     *
     * @param Writer $writer The XML writer.
     * @return void
     * @throws InvalidArgumentException if extension URI or content is not set.
     */
    public function xmlSerialize(Writer $writer): void
    {
        if ($this->extensionURI === null) {
            throw new InvalidArgumentException('Extension URI is required.');
        }
        if ($this->extensionContent === null) {
            throw new InvalidArgumentException('Extension content is required.');
        }

        $writer->write([
            [
                'name'  => Schema::EXT . 'ExtensionURI',
                'value' => $this->extensionURI,
            ],
            [
                'name'  => Schema::EXT . 'ExtensionContent',
                'value' => $this->extensionContent,
            ],
        ]);
    }
}
