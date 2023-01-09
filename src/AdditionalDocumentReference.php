<?php
namespace Saleh7\Zatca;

use Sabre\Xml\Writer;
use Sabre\Xml\XmlSerializable;

class AdditionalDocumentReference implements XmlSerializable
{
    private $id;
    private $UUID;
    private $documentType;
    private $previousInvoiceHash;
    private $attachment;

    /**
     * @param string $id
     * @return AdditionalDocumentReference
     */
    public function setId(?string $id): AdditionalDocumentReference
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @param string $id
     * @return AdditionalDocumentReference
     */
    public function setUUID(?string $UUID): AdditionalDocumentReference
    {
        $this->UUID = $UUID;
        return $this;
    }

    /**
     * @param string $documentType
     * @return AdditionalDocumentReference
     */
    public function setDocumentType(string $documentType): AdditionalDocumentReference
    {
        $this->documentType = $documentType;
        return $this;
    }

    /**
     * @param string $previousInvoiceHash
     * @return AdditionalDocumentReference
     */
    public function setPreviousInvoiceHash(string $previousInvoiceHash): AdditionalDocumentReference
    {
        $this->previousInvoiceHash = $previousInvoiceHash;
        return $this;
    }

    /**
     * @param Attachment $attachment
     * @return AdditionalDocumentReference
     */
    public function setAttachment(Attachment $attachment): AdditionalDocumentReference
    {
        $this->attachment = $attachment;
        return $this;
    }
    /**
     * The xmlSerialize method is called during xml writing.
     * @param Writer $writer
     * @return void
     */
    public function xmlSerialize(Writer $writer): void
    {
        $writer->write([
            Schema::CBC . 'ID' => $this->id
        ]);
        if ($this->UUID !== null) {
             $writer->write([
                 Schema::CBC . 'UUID' => $this->UUID
             ]);
         }
        if ($this->documentType !== null) {
             $writer->write([
                 Schema::CAC . 'DocumentType' => $this->documentType
             ]);
         }
        if ($this->previousInvoiceHash !== null) {
            $writer->write([
                'name' => Schema::CAC . 'Attachment',
                'value' => [
                    'name' => Schema::CBC . 'EmbeddedDocumentBinaryObject',
                    'value' => $this->previousInvoiceHash,
                    'attributes' => [
                        'mimeCode' => 'text/plain'
                    ]
                ],
            ]);
         }
         if ($this->attachment !== null) {
             $writer->write([
                 Schema::CAC . 'Attachment' => $this->attachment
             ]);
         }
    }
}
