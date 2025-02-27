<?php
namespace Saleh7\Zatca\Mappers;

use Saleh7\Zatca\{
    TaxScheme, LegalEntity, PartyTaxScheme, Address, Party
};

/**
 * Class CustomerMapper
 *
 * Maps customer data (array) to a Party object.
 */
class CustomerMapper
{
    /**
     * Maps customer data array to a Party object.
     *
     * Expected array structure:
     * [
     *   "taxScheme" => ["id" => "VAT"],
     *   "registrationName" => "Customer Name",
     *   "taxId" => "1234567890",
     *   "address" => [
     *       "street" => "Main Street",
     *       "buildingNumber" => "123",
     *       "subdivision" => "Subdivision",
     *       "city" => "City Name",
     *       "postalZone" => "12345",
     *       "country" => "SA"
     *   ],
     *   "identificationId" => "UniqueCustomerId", // optional
     *   "identificationType" => "IDType"          // optional
     * ]
     *
     * @param array $data Customer data.
     * @return Party The mapped customer as a Party object.
     */
    public function map(array $data): Party
    {

        if (empty($data)) {
            return new Party();
        }
        // Map the TaxScheme for the customer.
        $taxScheme = (new TaxScheme())
            ->setId($data['taxScheme']['id'] ?? "VAT");

        // Map the LegalEntity for the customer.
        $legalEntity = (new LegalEntity())
            ->setRegistrationName($data['registrationName'] ?? '');

        // Map the PartyTaxScheme for the customer.
        $partyTaxScheme = (new PartyTaxScheme())
            ->setTaxScheme($taxScheme)
            ->setCompanyId($data['taxId'] ?? '');

        // Map the Address for the customer.
        $address = (new Address())
            ->setStreetName($data['address']['street'] ?? '')
            ->setBuildingNumber($data['address']['buildingNumber'] ?? '')
            ->setCitySubdivisionName($data['address']['subdivision'] ?? '')
            ->setCityName($data['address']['city'] ?? '')
            ->setPostalZone($data['address']['postalZone'] ?? '')
            ->setCountry($data['address']['country'] ?? 'SA');

        // Create and populate the Party object.
        $party = (new Party())
            ->setLegalEntity($legalEntity)
            ->setPartyTaxScheme($partyTaxScheme)
            ->setPostalAddress($address);

        // Set party identification if available.
        if (isset($data['identificationId'])) {
            $party->setPartyIdentification($data['identificationId']);
            if (isset($data['identificationType'])) {
                $party->setPartyIdentificationId($data['identificationType']);
            }
        }
        return $party;
    }
}