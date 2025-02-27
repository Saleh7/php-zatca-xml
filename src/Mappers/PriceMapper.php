<?php
namespace Saleh7\Zatca\Mappers;

use Saleh7\Zatca\{
    Price, AllowanceCharge, UnitCode
};

/**
 * Class PriceMapper
 *
 * Maps price data (provided as an associative array) to a Price object.
 *
 * Expected input array structure:
 * [
 *   "unitCode"         => "UNIT",   // Optional, defaults to UnitCode::UNIT if not provided.
 *   "amount"           => 100.0,    // Price amount.
 *   "allowanceCharges" => [         // Optional, an array of allowance charge data.
 *       [
 *           "isCharge" => true,
 *           "reason"   => "discount",
 *           "amount"   => 5.0
 *       ],
 *       ...
 *   ]
 * ]
 *
 * @package Saleh7\Zatca\Mappers
 */
class PriceMapper
{
    /**
     * Map price data array to a Price object.
     *
     * @param array $data The price data.
     * @return Price The mapped Price object.
     */
    public function map(array $data): Price
    {
        // Create a new Price object and set the unit code and price amount.
        $price = (new Price())
            ->setUnitCode($data['unitCode'] ?? UnitCode::UNIT)
            ->setPriceAmount($data['amount'] ?? 0);
        
        // Map allowance charges if provided.
        if (isset($data['allowanceCharges']) && is_array($data['allowanceCharges'])) {
            $allowanceCharges = [];
            foreach ($data['allowanceCharges'] as $charge) {
                $allowanceCharges[] = (new AllowanceCharge())
                    ->setChargeIndicator($charge['isCharge'] ?? true)
                    ->setAllowanceChargeReason($charge['reason'] ?? 'discount')
                    ->setAmount($charge['amount'] ?? 0.00);
            }
            $price->setAllowanceCharges($allowanceCharges);
        }
        
        return $price;
    }
}