<?php
namespace Saleh7\Zatca;

use Sabre\Xml\Writer;
use Sabre\Xml\XmlSerializable;

class LegalEntity implements XmlSerializable
{
    private $registrationName;

    /**
     * @param string $registrationName
     * @return LegalEntity
     */
    public function setRegistrationName(?string $registrationName): LegalEntity
    {
        $this->registrationName = $registrationName;
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
        if ($this->registrationName !== null) {
            $writer->write( [ Schema::CBC . 'RegistrationName' => $this->registrationName ] );
        }
    }
}
