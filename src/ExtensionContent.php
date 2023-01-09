<?php
namespace Saleh7\Zatca;

use Sabre\Xml\Writer;
use Sabre\Xml\XmlSerializable;

class ExtensionContent implements XmlSerializable
{

    private $UBLDocumentSignatures;

    /**
     * @param UBLDocumentSignatures $UBLDocumentSignatures
     * @return ExtensionContent
     */
    public function setUBLDocumentSignatures(UBLDocumentSignatures $UBLDocumentSignatures): ExtensionContent
    {
        $this->UBLDocumentSignatures = $UBLDocumentSignatures;
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
            Schema::SIG . 'UBLDocumentSignatures' => $this->UBLDocumentSignatures
        ]);
    }
}
