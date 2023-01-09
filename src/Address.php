<?php
namespace Saleh7\Zatca;

use Sabre\Xml\Writer;
use Sabre\Xml\XmlSerializable;

class Address implements XmlSerializable
{
    private $streetName;
    private $additionalStreetName;
    private $buildingNumber;
    private $plotIdentification;
    private $cityName;
    private $postalZone;
    private $country;
    private $countrySubentity;
    private $citySubdivisionName;

    /**
     * @param string $streetName
     * @return Address
     */
    public function setStreetName(?string $streetName): Address
    {
        $this->streetName = $streetName;
        return $this;
    }

    /**
     * @param string $additionalStreetName
     * @return Address
     */
    public function setAdditionalStreetName(?string $additionalStreetName): Address
    {
        $this->additionalStreetName = $additionalStreetName;
        return $this;
    }

    /**
     * @param string $buildingNumber
     * @return Address
     */
    public function setBuildingNumber(?string $buildingNumber): Address
    {
        $this->buildingNumber = $buildingNumber;
        return $this;
    }

    /**
     * @param string $plotIdentification
     * @return Address
     */
    public function setPlotIdentification(?string $plotIdentification): Address
    {
        $this->plotIdentification = $plotIdentification;
        return $this;
    }

    /**
     * @param string $cityName
     * @return Address
     */
    public function setCityName(?string $cityName): Address
    {
        $this->cityName = $cityName;
        return $this;
    }

    /**
     * @param string $postalZone
     * @return Address
     */
    public function setPostalZone(?string $postalZone): Address
    {
        $this->postalZone = $postalZone;
        return $this;
    }

    /**
     * @param string $country
     * @return Address
     */
    public function setCountry(?string $country): Address
    {
        $this->country = $country;
        return $this;
    }

    /**
     * @param string $countrySubentity
     * @return Address
     */
    public function setCountrySubentity(?string $countrySubentity): Address
    {
        $this->countrySubentity = $countrySubentity;
        return $this;
    }
    /**
     * @param string $citySubdivisionName
     * @return Address
     */
    public function setCitySubdivisionName(?string $citySubdivisionName): Address
    {
        $this->citySubdivisionName = $citySubdivisionName;
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
        if ($this->streetName !== null) {
            $writer->write([
                Schema::CBC . 'StreetName' => $this->streetName
            ]);
        }
        if ($this->buildingNumber !== null) {
            $writer->write([
                Schema::CBC . 'BuildingNumber' => $this->buildingNumber
            ]);
        }
        if ($this->plotIdentification !== null) {
            $writer->write([
                Schema::CBC . 'PlotIdentification' => $this->plotIdentification
            ]);
        }
        if ($this->countrySubentity !== null) {
            $writer->write([
                Schema::CBC . 'CountrySubentity' => $this->countrySubentity
            ]);
        }
        if ($this->citySubdivisionName !== null) {
            $writer->write([
                Schema::CBC . 'CitySubdivisionName' => $this->citySubdivisionName
            ]);
        }
        if ($this->additionalStreetName !== null) {
            $writer->write([
                Schema::CBC . 'AdditionalStreetName' => $this->additionalStreetName
            ]);
        }

        if ($this->cityName !== null) {
            $writer->write([
                Schema::CBC . 'CityName' => $this->cityName,
            ]);
        }
        if ($this->postalZone !== null) {
            $writer->write([
                Schema::CBC . 'PostalZone' => $this->postalZone,
            ]);
        }
        if ($this->country !== null) {
            $writer->write([
                Schema::CAC . 'Country' => [Schema::CBC . 'IdentificationCode' => $this->country],
            ]);
        }
    }
}
