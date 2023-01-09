<?php
namespace Saleh7\Zatca;

use Sabre\Xml\Writer;
use Sabre\Xml\XmlSerializable;

class UBLExtension implements XmlSerializable
{
    private $extensionURI;
    private $extensionContent;

    /**
     * @return string
     */
    public function getExtensionURI(): string
    {
        return $this->extensionURI;
    }

    /**
     * @param string $uri
     * @return UBLExtension
     */
    public function setExtensionURI(string $uri): UBLExtension
    {
        $this->extensionURI = $uri;
        return $this;
    }

    /**
     * @return ExtensionContent
     */
    public function getExtensionContent(): ExtensionContent
    {
        return $this->extensionContent;
    }

    /**
     * @param ExtensionContent $extensionContent
     * @return UBLExtension
     */
    public function setExtensionContent(ExtensionContent $extensionContent): UBLExtension
    {
        $this->extensionContent = $extensionContent;
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
        $writer->write([
            [Schema::EXT . 'ExtensionURI' => $this->extensionURI],
            [Schema::EXT . 'ExtensionContent' => $this->extensionContent]
        ]);
    }
}
