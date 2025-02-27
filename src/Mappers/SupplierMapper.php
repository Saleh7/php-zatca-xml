<?php
namespace Saleh7\Zatca\Mappers;

use Saleh7\Zatca\{
    TaxScheme, LegalEntity, PartyTaxScheme, Address, Party
};

/**
 * Class SupplierMapper
 *
 * Maps supplier data (provided as an associative array) to a Party object.
 *
 * Expected input array structure:
 * [
 *   "taxScheme" => ["id" => "VAT"],
 *   "registrationName" => "Supplier Name",
 *   "taxId" => "1234567890",
 *   "address" => [
 *       "street" => "Main Street",
 *       "buildingNumber" => "123",
 *       "subdivision" => "Subdivision Name",
 *       "city" => "City Name",
 *       "postalZone" => "12345",
 *       "country" => "SA"
 *   ],
 *   "identificationId" => "SupplierUniqueID",  // Optional, defaults to empty string if not provided.
 *   "identificationType" => "CRN"              // Optional, defaults to "CRN" if not provided.
 * ]
 *
 * @package Saleh7\Zatca\Mappers
 */
class SupplierMapper
{
    /**
     * Map supplier data array to a Party object.
     *
     * @param array $data Supplier data.
     * @return Party The mapped supplier as a Party object.
     */
    public function map(array $data): Party
    {
        // Map the TaxScheme for the supplier.
        $taxScheme = (new TaxScheme())
            ->setId($data['taxScheme']['id'] ?? "VAT");

        // Map the LegalEntity for the supplier.
        $legalEntity = (new LegalEntity())
            ->setRegistrationName($data['registrationName'] ?? '');

        // Map the PartyTaxScheme for the supplier.
        $partyTaxScheme = (new PartyTaxScheme())
            ->setTaxScheme($taxScheme)
            ->setCompanyId($data['taxId'] ?? '');

        // Map the Address for the supplier.
        $address = (new Address())
            ->setStreetName($data['address']['street'] ?? '')
            ->setBuildingNumber($data['address']['buildingNumber'] ?? '')
            ->setCitySubdivisionName($data['address']['subdivision'] ?? '')
            ->setCityName($data['address']['city'] ?? '')
            ->setPostalZone($data['address']['postalZone'] ?? '')
            ->setCountry($data['address']['country'] ?? 'SA');

        // Create and return the Party object with the mapped data.
        return (new Party())
            ->setPartyIdentification($data['identificationId'] ?? "")
            ->setPartyIdentificationId($data['identificationType'] ?? "CRN")
            ->setLegalEntity($legalEntity)
            ->setPartyTaxScheme($partyTaxScheme)
            ->setPostalAddress($address);
    }
}
