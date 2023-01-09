<?php
namespace Saleh7\Zatca;

use Sabre\Xml\Writer;
use Sabre\Xml\XmlSerializable;

class BillingReference implements XmlSerializable
{
    private $id;

    /**
     * @param string $id
     * @return BillingReference
     */
    public function setId(string $id): BillingReference
    {
        $this->id = $id;
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
        if ($this->id !== null) {
            $writer->write([ Schema::CAC . 'InvoiceDocumentReference' => [ Schema::CBC . 'ID' => $this->id ] ]);
        }
    }
}
