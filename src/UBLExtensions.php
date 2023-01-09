<?php
namespace Saleh7\Zatca;

use InvalidArgumentException;
use Sabre\Xml\Writer;
use Sabre\Xml\XmlSerializable;

class UBLExtensions implements XmlSerializable
{

    private array $UBLExtensions;

    /**
     * @return UBLExtension
     */
    public function getUBLExtensions()
    {
        return $this->UBLExtensions;
    }

    /**
     * @param UBLExtension[] $UBLExtensions
     * @return UBLExtensions
     */
    public function setUBLExtensions(array $UBLExtensions): UBLExtensions
    {
        $this->UBLExtensions = $UBLExtensions;
        return $this;
    }

    private function validator()
    {
        if ($this->UBLExtensions === null) {
            throw new InvalidArgumentException("Messing UBL Extension");
        }
    }

    /**
     * The xmlSerialize method is called during xml writing.
     *
     * @param Writer $writer
     * @return void
     */
    public function xmlSerialize(Writer $writer): void
    {
        $this->validator();
        foreach ($this->UBLExtensions as $UBLExtension) {
            $writer->write([
                Schema::EXT . 'UBLExtension' => $UBLExtension
            ]);
        }
    }
}
