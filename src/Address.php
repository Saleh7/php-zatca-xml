<?php
namespace Saleh7\Zatca;

use InvalidArgumentException;
use Sabre\Xml\Writer;
use Sabre\Xml\XmlSerializable;

/**
 * Class Address
 *
 * Represents an address for XML serialization.
 */
class Address implements XmlSerializable
{
    /** @var string|null Street name. */
    private ?string $streetName = null;

    /** @var string|null Additional street name. */
    private ?string $additionalStreetName = null;

    /** @var string|null Building number. */
    private ?string $buildingNumber = null;

    /** @var string|null Plot identification. */
    private ?string $plotIdentification = null;

    /** @var string|null City name. */
    private ?string $cityName = null;

    /** @var string|null Postal zone. */
    private ?string $postalZone = null;

    /** @var string|null Country code. */
    private ?string $country = null;

    /** @var string|null Country subentity. */
    private ?string $countrySubentity = null;

    /** @var string|null City subdivision name. */
    private ?string $citySubdivisionName = null;

    /**
     * Set the street name.
     *
     * @param string|null $streetName
     * @return self
     * @throws InvalidArgumentException if $streetName is an empty string.
     */
    public function setStreetName(?string $streetName): self
    {
        if ($streetName !== null && trim($streetName) === '') {
            throw new InvalidArgumentException('Street name cannot be empty.');
        }
        $this->streetName = $streetName;
        return $this;
    }

    /**
     * Set the additional street name.
     *
     * @param string|null $additionalStreetName
     * @return self
     */
    public function setAdditionalStreetName(?string $additionalStreetName): self
    {
        if ($additionalStreetName !== null && trim($additionalStreetName) === '') {
            throw new InvalidArgumentException('Additional street name cannot be empty.');
        }
        $this->additionalStreetName = $additionalStreetName;
        return $this;
    }

    /**
     * Set the building number.
     *
     * @param string|null $buildingNumber
     * @return self
     */
    public function setBuildingNumber(?string $buildingNumber): self
    {
        if ($buildingNumber !== null && trim($buildingNumber) === '') {
            throw new InvalidArgumentException('Building number cannot be empty.');
        }
        $this->buildingNumber = $buildingNumber;
        return $this;
    }

    /**
     * Set the plot identification.
     *
     * @param string|null $plotIdentification
     * @return self
     */
    public function setPlotIdentification(?string $plotIdentification): self
    {
        if ($plotIdentification !== null && trim($plotIdentification) === '') {
            throw new InvalidArgumentException('Plot identification cannot be empty.');
        }
        $this->plotIdentification = $plotIdentification;
        return $this;
    }

    /**
     * Set the city name.
     *
     * @param string|null $cityName
     * @return self
     */
    public function setCityName(?string $cityName): self
    {
        if ($cityName !== null && trim($cityName) === '') {
            throw new InvalidArgumentException('City name cannot be empty.');
        }
        $this->cityName = $cityName;
        return $this;
    }

    /**
     * Set the postal zone.
     *
     * @param string|null $postalZone
     * @return self
     */
    public function setPostalZone(?string $postalZone): self
    {
        if ($postalZone !== null && trim($postalZone) === '') {
            throw new InvalidArgumentException('Postal zone cannot be empty.');
        }
        $this->postalZone = $postalZone;
        return $this;
    }

    /**
     * Set the country code.
     *
     * @param string|null $country
     * @return self
     */
    public function setCountry(?string $country): self
    {
        if ($country !== null && trim($country) === '') {
            throw new InvalidArgumentException('Country cannot be empty.');
        }
        $this->country = $country;
        return $this;
    }

    /**
     * Set the country subentity.
     *
     * @param string|null $countrySubentity
     * @return self
     */
    public function setCountrySubentity(?string $countrySubentity): self
    {
        if ($countrySubentity !== null && trim($countrySubentity) === '') {
            throw new InvalidArgumentException('Country subentity cannot be empty.');
        }
        $this->countrySubentity = $countrySubentity;
        return $this;
    }

    /**
     * Set the city subdivision name.
     *
     * @param string|null $citySubdivisionName
     * @return self
     */
    public function setCitySubdivisionName(?string $citySubdivisionName): self
    {
        if ($citySubdivisionName !== null && trim($citySubdivisionName) === '') {
            throw new InvalidArgumentException('City subdivision name cannot be empty.');
        }
        $this->citySubdivisionName = $citySubdivisionName;
        return $this;
    }

    /**
     * Get the street name.
     *
     * @return string|null
     */
    public function getStreetName(): ?string
    {
        return $this->streetName;
    }

    /**
     * Get the additional street name.
     *
     * @return string|null
     */
    public function getAdditionalStreetName(): ?string
    {
        return $this->additionalStreetName;
    }

    /**
     * Get the building number.
     *
     * @return string|null
     */
    public function getBuildingNumber(): ?string
    {
        return $this->buildingNumber;
    }

    /**
     * Get the plot identification.
     *
     * @return string|null
     */
    public function getPlotIdentification(): ?string
    {
        return $this->plotIdentification;
    }

    /**
     * Get the city name.
     *
     * @return string|null
     */
    public function getCityName(): ?string
    {
        return $this->cityName;
    }

    /**
     * Get the postal zone.
     *
     * @return string|null
     */
    public function getPostalZone(): ?string
    {
        return $this->postalZone;
    }

    /**
     * Get the country code.
     *
     * @return string|null
     */
    public function getCountry(): ?string
    {
        return $this->country;
    }

    /**
     * Get the country subentity.
     *
     * @return string|null
     */
    public function getCountrySubentity(): ?string
    {
        return $this->countrySubentity;
    }

    /**
     * Get the city subdivision name.
     *
     * @return string|null
     */
    public function getCitySubdivisionName(): ?string
    {
        return $this->citySubdivisionName;
    }

    /**
     * Serializes this object to XML.
     *
     * @param Writer $writer
     * @return void
     */
    public function xmlSerialize(Writer $writer): void
    {
        if ($this->streetName !== null) {
            $writer->write([
                Schema::CBC . 'StreetName' => $this->streetName,
            ]);
        }
        if ($this->buildingNumber !== null) {
            $writer->write([
                Schema::CBC . 'BuildingNumber' => $this->buildingNumber,
            ]);
        }
        if ($this->plotIdentification !== null) {
            $writer->write([
                Schema::CBC . 'PlotIdentification' => $this->plotIdentification,
            ]);
        }
        if ($this->countrySubentity !== null) {
            $writer->write([
                Schema::CBC . 'CountrySubentity' => $this->countrySubentity,
            ]);
        }
        if ($this->citySubdivisionName !== null) {
            $writer->write([
                Schema::CBC . 'CitySubdivisionName' => $this->citySubdivisionName,
            ]);
        }
        if ($this->additionalStreetName !== null) {
            $writer->write([
                Schema::CBC . 'AdditionalStreetName' => $this->additionalStreetName,
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
                Schema::CAC . 'Country' => [
                    Schema::CBC . 'IdentificationCode' => $this->country,
                ],
            ]);
        }
    }
}
