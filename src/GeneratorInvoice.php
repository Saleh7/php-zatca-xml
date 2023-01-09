<?php
namespace Saleh7\Zatca;

use Sabre\Xml\Service;

class GeneratorInvoice
{
    public static $currencyID;

    public static function invoice(Invoice $invoice, $currencyId = 'SAR')
    {
        self::$currencyID = $currencyId;

        $xmlService = new Service();

        $xmlService->namespaceMap = [
            'urn:oasis:names:specification:ubl:schema:xsd:Invoice-2' => '',
            'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2' => 'cac',
            'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2' => 'cbc',
            'urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2' => 'ext'
        ];

        return $xmlService->write('Invoice', [
            $invoice
        ]);
    }
}
