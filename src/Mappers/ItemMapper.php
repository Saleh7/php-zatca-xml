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
        // Map classified tax category for the item.
        // if percent 15 or more, ID is S (Standard rate)
        // if percent 6 to 14.99, ID is AA (Reduced rate)
        // if percent less than 6, ID is Z (Zero rate)
        $classifiedTax = [];
        if (isset($data['classifiedTaxCategory']) && is_array($data['classifiedTaxCategory'])) {
            foreach ($data['classifiedTaxCategory'] as $tax) {
                // Map TaxScheme for the item.
                $taxScheme = (new TaxScheme())
                ->setId($tax['taxScheme']['id'] ?? "VAT");
                // Create and add a new ClassifiedTaxCategory object to the array.
                $classifiedTax[] = (new ClassifiedTaxCategory())
                    ->setPercent($tax['percent'] ?? 15)
                    ->setTaxScheme($taxScheme);
            }
        }
        // Create and return the Item object with mapped data.
        return (new Item())
            ->setName($data['name'] ?? 'Product')
            ->setClassifiedTaxCategory($classifiedTax);
    }
}