<?php
namespace Saleh7\Zatca;

use Sabre\Xml\Writer;
use Sabre\Xml\XmlSerializable;

class UBLDocumentSignatures implements XmlSerializable
{

    private $signatureInformation;

    /**
     * @param SignatureInformation $signatureInformation
     * @return UBLDocumentSignatures
     */
    public function setSignatureInformation(SignatureInformation $signatureInformation): UBLDocumentSignatures
    {
        $this->signatureInformation = $signatureInformation;
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
            Schema::SAC . 'SignatureInformation' => $this->signatureInformation
        ]);
    }
}
