<?php
namespace Saleh7\Zatca\Mappers;

use Saleh7\Zatca\PaymentMeans;

/**
 * Class PaymentMeansMapper
 *
 * Maps payment means data provided as an associative array to a PaymentMeans object.
 *
 * Expected input array structure:
 * [
 *     "code" => "10" // Payment means code, e.g., "10" for cash.
 * ]
 *
 * @package Saleh7\Zatca\Mappers
 */
class PaymentMeansMapper
{
    /**
     * Map payment means data array to a PaymentMeans object.
     *
     * @param array $data The payment means data.
     * @return PaymentMeans The mapped PaymentMeans object.
     */
    public function map(array $data): PaymentMeans
    {
        return (new PaymentMeans())
            ->setPaymentMeansCode($data['code'] ?? "10")
            ->setInstructionNote($data['note'] ?? null);
    }
}