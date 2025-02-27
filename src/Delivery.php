<?php
namespace Saleh7\Zatca;

use DateTime;
use InvalidArgumentException;
use Sabre\Xml\Writer;
use Sabre\Xml\XmlSerializable;

/**
 * Class Delivery
 *
 * Represents a delivery with actual/latest delivery dates and a location.
 */
class Delivery implements XmlSerializable
{
    /** @var DateTime|null Actual delivery date. */
    private ?DateTime $actualDeliveryDate = null;

    /** @var DateTime|null Latest delivery date. */
    private ?DateTime $latestDeliveryDate = null;

    /** @var Address|null Delivery location. */
    private ?Address $deliveryLocation = null;

    /**
     * Set the actual delivery date.
     *
     * Accepts a DateTime instance or a date string (which will be converted to DateTime).
     *
     * @param DateTime|string|null $actualDeliveryDate
     * @return self
     * @throws InvalidArgumentException if the date string is invalid.
     */
    public function setActualDeliveryDate(DateTime|string|null $actualDeliveryDate): self
    {
        if (is_string($actualDeliveryDate)) {
            try {
                $actualDeliveryDate = new DateTime($actualDeliveryDate);
            } catch (\Exception $e) {
                throw new InvalidArgumentException('Invalid actual delivery date format.');
            }
        }
        $this->actualDeliveryDate = $actualDeliveryDate;
        return $this;
    }

    /**
     * Get the actual delivery date.
     *
     * @return DateTime|null
     */
    public function getActualDeliveryDate(): ?DateTime
    {
        return $this->actualDeliveryDate;
    }

    /**
     * Set the latest delivery date.
     *
     * Accepts a DateTime instance or a date string (which will be converted to DateTime).
     *
     * @param DateTime|string|null $latestDeliveryDate
     * @return self
     * @throws InvalidArgumentException if the date string is invalid.
     */
    public function setLatestDeliveryDate(DateTime|string|null $latestDeliveryDate): self
    {
        if (is_string($latestDeliveryDate)) {
            try {
                $latestDeliveryDate = new DateTime($latestDeliveryDate);
            } catch (\Exception $e) {
                throw new InvalidArgumentException('Invalid latest delivery date format.');
            }
        }
        $this->latestDeliveryDate = $latestDeliveryDate;
        return $this;
    }

    /**
     * Get the latest delivery date.
     *
     * @return DateTime|null
     */
    public function getLatestDeliveryDate(): ?DateTime
    {
        return $this->latestDeliveryDate;
    }

    /**
     * Set the delivery location.
     *
     * @param Address|null $deliveryLocation
     * @return self
     */
    public function setDeliveryLocation(?Address $deliveryLocation): self
    {
        $this->deliveryLocation = $deliveryLocation;
        return $this;
    }

    /**
     * Get the delivery location.
     *
     * @return Address|null
     */
    public function getDeliveryLocation(): ?Address
    {
        return $this->deliveryLocation;
    }

    /**
     * Serializes this object to XML.
     *
     * Dates are formatted as 'Y-m-d'. Adjust the format if needed.
     *
     * @param Writer $writer
     * @return void
     */
    public function xmlSerialize(Writer $writer): void
    {
        $data = [];

        if ($this->actualDeliveryDate !== null) {
            $data[Schema::CBC . 'ActualDeliveryDate'] = $this->actualDeliveryDate->format('Y-m-d');
        }

        if ($this->latestDeliveryDate !== null) {
            $data[Schema::CBC . 'LatestDeliveryDate'] = $this->latestDeliveryDate->format('Y-m-d');
        }
        
        if ($this->deliveryLocation !== null) {
            $data[Schema::CAC . 'DeliveryLocation'] = [
                Schema::CAC . 'Address' => $this->deliveryLocation,
            ];
        }

        $writer->write($data);
    }
}
