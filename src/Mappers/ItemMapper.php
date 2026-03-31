<?php
namespace Saleh7\Zatca\Mappers;

use Saleh7\Zatca\{
    Item, TaxScheme, ClassifiedTaxCategory
};

/**
 * Class ItemMapper
 *
 * Maps item data provided as an associative array to an Item object.
 *
 * Expected input array structure:
 * [
 *     "name" => "Product Name",
 *     "taxScheme" => [
 *         "id" => "VAT"
 *     ],
 *     "taxPercent" => 15
 * ]
 *
 * @package Saleh7\Zatca\Mappers
 */
class ItemMapper
{
    /**
     * Map item data array to an Item object.
     *
     * @param array $data The item data.
     * @return Item The mapped Item object.
     */
    public function map(array $data): Item
    {
        $classifiedTax = [];
        if (isset($data['classifiedTaxCategory']) && is_array($data['classifiedTaxCategory'])) {
            foreach ($data['classifiedTaxCategory'] as $tax) {
                $taxScheme = (new TaxScheme())
                    ->setId($tax['taxScheme']['id'] ?? 'VAT');

                $taxCategory = (new ClassifiedTaxCategory())
                    ->setPercent($tax['percent'] ?? 15)
                    ->setTaxScheme($taxScheme);

                // Allow explicit ZATCA VAT category code (S, Z, E, O)
                if (isset($tax['id'])) {
                    $taxCategory->setId($tax['id']);
                }

                $classifiedTax[] = $taxCategory;
            }
        }
        // Create and return the Item object with mapped data.
        return (new Item())
            ->setName($data['name'] ?? 'Product')
            ->setClassifiedTaxCategory($classifiedTax);
    }
}