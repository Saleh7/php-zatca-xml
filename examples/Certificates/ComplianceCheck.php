<?php
/**
 * ZATCA Compliance Check & Production Certificate Request
 *
 * This script:
 * 1. Generates 6 test invoices (all types required by ZATCA)
 * 2. Signs each with the compliance certificate
 * 3. Submits each to ZATCA compliance validation
 * 4. If all pass, requests a production certificate (PCSID)
 * 5. Saves production credentials to output/production_credentials.json
 *
 * Prerequisites:
 *   - Run GeneratorCertificate.php first (creates CSR + private key)
 *   - Run RequestComplianceCertificate.php first (creates compliance credentials)
 */

require __DIR__ . '/../../vendor/autoload.php';

use Saleh7\Zatca\Mappers\InvoiceMapper;
use Saleh7\Zatca\GeneratorInvoice;
use Saleh7\Zatca\Helpers\Certificate;
use Saleh7\Zatca\InvoiceSigner;
use Saleh7\Zatca\ZatcaAPI;
use Saleh7\Zatca\Exceptions\ZatcaApiException;

// ─── Configuration ─────────────────────────────────────────────────────

$environment = 'sandbox'; // Change to 'production' for real onboarding

$credentialsPath = __DIR__ . '/output/ZATCA_certificate_data.json';
$privateKeyPath  = __DIR__ . '/output/private.pem';
$outputDir       = __DIR__ . '/output/compliance';

// Supplier info (same as your certificate)
$supplier = [
    'registrationName' => 'Maximum Speed Tech Supply LTD',
    'taxId'            => '399999999900003',
    'identificationId' => '1010010000',
    'identificationType' => 'CRN',
    'address' => [
        'street'         => 'Prince Sultan',
        'buildingNumber' => '2322',
        'subdivision'    => 'Ar Rabi',
        'city'           => 'Riyadh',
        'postalZone'     => '23333',
        'country'        => 'SA',
    ],
];

// ─── Load Credentials ──────────────────────────────────────────────────

if (!file_exists($credentialsPath)) {
    die("Error: Run RequestComplianceCertificate.php first.\n");
}
if (!file_exists($privateKeyPath)) {
    die("Error: Run GeneratorCertificate.php first.\n");
}

$credentials = json_decode(file_get_contents($credentialsPath), true);
$privateKeyPem = file_get_contents($privateKeyPath);
$cleanPrivateKey = trim(preg_replace('/-----(?:BEGIN|END)(?: EC)? PRIVATE KEY-----/', '', $privateKeyPem));

if (!is_dir($outputDir)) {
    mkdir($outputDir, 0755, true);
}

// ─── Define 6 Test Invoices ────────────────────────────────────────────

$testInvoices = [
    // 1. Standard Invoice
    [
        'label' => '1/6 Standard Invoice',
        'data'  => buildInvoice($supplier, [
            'id' => 'SME00001', 'type' => 'standard', 'subtype' => 'invoice',
            'hasCustomer' => true, 'hasDelivery' => true,
        ]),
    ],
    // 2. Standard Credit Note
    [
        'label' => '2/6 Standard Credit Note',
        'data'  => buildInvoice($supplier, [
            'id' => 'SME00002', 'type' => 'standard', 'subtype' => 'credit',
            'hasCustomer' => true, 'hasDelivery' => true, 'billingRef' => 'SME00001',
        ]),
    ],
    // 3. Standard Debit Note
    [
        'label' => '3/6 Standard Debit Note',
        'data'  => buildInvoice($supplier, [
            'id' => 'SME00003', 'type' => 'standard', 'subtype' => 'debit',
            'hasCustomer' => true, 'hasDelivery' => true, 'billingRef' => 'SME00001',
        ]),
    ],
    // 4. Simplified Invoice
    [
        'label' => '4/6 Simplified Invoice',
        'data'  => buildInvoice($supplier, [
            'id' => 'SME00004', 'type' => 'simplified', 'subtype' => 'invoice',
        ]),
    ],
    // 5. Simplified Credit Note
    [
        'label' => '5/6 Simplified Credit Note',
        'data'  => buildInvoice($supplier, [
            'id' => 'SME00005', 'type' => 'simplified', 'subtype' => 'credit',
            'billingRef' => 'SME00004',
        ]),
    ],
    // 6. Simplified Debit Note
    [
        'label' => '6/6 Simplified Debit Note',
        'data'  => buildInvoice($supplier, [
            'id' => 'SME00006', 'type' => 'simplified', 'subtype' => 'debit',
            'billingRef' => 'SME00004',
        ]),
    ],
];

// ─── Process Each Invoice ──────────────────────────────────────────────

$api = new ZatcaAPI($environment);
$allPassed = true;
$icv = 0;

echo "╔══════════════════════════════════════════════════════╗\n";
echo "║       ZATCA Compliance Check — 6 Invoice Types      ║\n";
echo "╚══════════════════════════════════════════════════════╝\n\n";

foreach ($testInvoices as $i => $test) {
    $icv++;
    $label = $test['label'];
    $invoiceData = $test['data'];

    // Update ICV counter
    $invoiceData['additionalDocuments'][0]['uuid'] = (string)$icv;

    echo "── {$label} ──────────────────────────────\n";

    try {
        // 1. Generate XML
        $invoice = (new InvoiceMapper())->mapToInvoice($invoiceData);
        GeneratorInvoice::invoice($invoice)->saveXMLFile("{$invoiceData['id']}.xml", $outputDir);
        echo "   ✓ XML generated\n";

        // 2. Sign
        $xmlContent = file_get_contents("{$outputDir}/{$invoiceData['id']}.xml");
        $certificate = new Certificate($credentials['certificate'], $cleanPrivateKey, $credentials['secret']);
        $signer = InvoiceSigner::signInvoice($xmlContent, $certificate);
        $signer->saveXMLFile("{$invoiceData['id']}_signed.xml", $outputDir);
        echo "   ✓ Signed\n";

        $signedXml   = $signer->getInvoice();
        $invoiceHash = $signer->getHash();
        $uuid        = $invoiceData['uuid'];

        // 3. Submit to compliance
        $result = $api->validateInvoiceCompliance(
            $credentials['certificate'],
            $credentials['secret'],
            $signedXml,
            $invoiceHash,
            $uuid
        );

        $status = $result->getValidationStatus() ?? 'UNKNOWN';
        $errors = $result->getErrorMessages();
        $warnings = $result->getWarningMessages();

        if ($result->isSuccess() || $result->getStatusCode() === 202) {
            echo "   ✓ ZATCA: {$status}";
            if (!empty($warnings)) {
                echo " (" . count($warnings) . " warnings)";
            }
            echo "\n";
        } else {
            echo "   ✗ ZATCA: {$status}\n";
            foreach ($errors as $err) {
                echo "     Error: " . ($err['message'] ?? json_encode($err)) . "\n";
            }
            $allPassed = false;
        }

    } catch (ZatcaApiException $e) {
        echo "   ✗ API Error: {$e->getMessage()}\n";
        $context = $e->getContext();
        if (!empty($context['response'])) {
            echo "     Response: " . json_encode($context['response'], JSON_UNESCAPED_UNICODE) . "\n";
        }
        $allPassed = false;
    } catch (\Throwable $e) {
        echo "   ✗ Error: {$e->getMessage()}\n";
        $allPassed = false;
    }

    echo "\n";
}

// ─── Request Production Certificate ────────────────────────────────────

if (!$allPassed) {
    echo "══════════════════════════════════════════════════════\n";
    echo "  ✗ Some invoices failed. Fix errors and retry.\n";
    echo "══════════════════════════════════════════════════════\n";
    exit(1);
}

echo "══════════════════════════════════════════════════════\n";
echo "  ✓ All 6 invoices passed compliance!\n";
echo "  → Requesting Production Certificate (PCSID)...\n\n";

try {
    $prodResult = $api->requestProductionCertificate(
        $credentials['certificate'],
        $credentials['secret'],
        $credentials['requestId']
    );

    $prodOutputFile = __DIR__ . '/output/production_credentials.json';

    // Reset storage base path (may have been set by GeneratorInvoice)
    \Saleh7\Zatca\Storage::setBasePath('');

    $api->saveToJson(
        $prodResult->getCertificate(),
        $prodResult->getSecret(),
        $prodResult->getRequestId(),
        $prodOutputFile
    );

    echo "  ✓ Production Certificate obtained!\n";
    echo "  ✓ Saved to: {$prodOutputFile}\n";
    echo "  → Request ID: {$prodResult->getRequestId()}\n\n";
    echo "══════════════════════════════════════════════════════\n";
    echo "  You can now submit real invoices using:\n";
    echo "    - production_credentials.json (certificate + secret)\n";
    echo "    - output/private.pem (same private key)\n";
    echo "══════════════════════════════════════════════════════\n";

} catch (ZatcaApiException $e) {
    echo "  ✗ Failed to get production certificate: {$e->getMessage()}\n";
    $context = $e->getContext();
    if (!empty($context['response'])) {
        echo "  Response: " . json_encode($context['response'], JSON_UNESCAPED_UNICODE) . "\n";
    }
    exit(1);
}

// ═══════════════════════════════════════════════════════════════════════
// Invoice Builder
// ═══════════════════════════════════════════════════════════════════════

function buildInvoice(array $supplier, array $opts): array
{
    $uuid = sprintf(
        '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000, mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );

    $now = new \DateTimeImmutable('now', new \DateTimeZone('Asia/Riyadh'));

    $invoice = [
        'uuid'            => $uuid,
        'id'              => $opts['id'],
        'issueDate'       => $now->format('Y-m-d'),
        'issueTime'       => $now->format('H:i:s'),
        'currencyCode'    => 'SAR',
        'taxCurrencyCode' => 'SAR',
        'invoiceType' => [
            'invoice'      => $opts['type'],       // 'standard' or 'simplified'
            'type'         => $opts['subtype'],     // 'invoice', 'credit', or 'debit'
            'isThirdParty' => false,
            'isNominal'    => false,
            'isExport'     => false,
            'isSummary'    => false,
            'isSelfBilled' => false,
        ],
        'additionalDocuments' => [
            ['id' => 'ICV', 'uuid' => '1'],
            [
                'id' => 'PIH',
                'attachment' => [
                    'content' => 'NWZlY2ViNjZmZmM4NmYzOGQ5NTI3ODZjNmQ2OTZjNzljMmRiYzIzOWRkNGU5MWI0NjcyOWQ3M2EyN2ZiNTdlOQ==',
                ],
            ],
        ],
        'supplier'     => $supplier,
        'paymentMeans' => ['code' => '10'],
        'allowanceCharges' => [
            [
                'isCharge' => false,
                'reason'   => 'discount',
                'amount'   => 0.00,
                'taxCategories' => [
                    ['percent' => 15, 'taxScheme' => ['id' => 'VAT']],
                ],
            ],
        ],
        'taxTotal' => [
            'taxAmount' => 1.50,
            'subTotals' => [[
                'taxableAmount' => 10.00,
                'taxAmount'     => 1.50,
                'taxCategory'   => ['percent' => 15, 'taxScheme' => ['id' => 'VAT']],
            ]],
        ],
        'legalMonetaryTotal' => [
            'lineExtensionAmount'  => 10.00,
            'taxExclusiveAmount'   => 10.00,
            'taxInclusiveAmount'   => 11.50,
            'prepaidAmount'        => 0,
            'payableAmount'        => 11.50,
            'allowanceTotalAmount' => 0,
        ],
        'invoiceLines' => [
            [
                'id' => 1, 'unitCode' => 'PCE', 'quantity' => 1, 'lineExtensionAmount' => 10.00,
                'item' => [
                    'name' => 'عسل طبيعي',
                    'classifiedTaxCategory' => [['percent' => 15, 'taxScheme' => ['id' => 'VAT']]],
                ],
                'price' => ['amount' => 10.00, 'unitCode' => 'UNIT'],
                'taxTotal' => ['taxAmount' => 1.50, 'roundingAmount' => 11.50],
            ],
        ],
    ];

    // Credit/Debit notes need a billing reference
    if (!empty($opts['billingRef'])) {
        $invoice['billingReferences'] = [['id' => $opts['billingRef']]];
    }

    // Credit notes need payment note
    if ($opts['subtype'] === 'credit') {
        $invoice['paymentMeans']['note'] = 'CANCELLATION_OR_TERMINATION';
    }
    if ($opts['subtype'] === 'debit') {
        $invoice['paymentMeans']['note'] = 'CANCELLATION_OR_TERMINATION';
    }

    // Standard invoices need customer + delivery
    if (!empty($opts['hasCustomer']) || $opts['type'] === 'standard') {
        $invoice['customer'] = [
            'registrationName' => 'شركة نماذج فاتورة المحدودة',
            'taxId'            => '399999999800003',
            'address' => [
                'street'         => 'صلاح الدين',
                'buildingNumber' => '1111',
                'subdivision'    => 'Al-Murooj',
                'city'           => 'Riyadh',
                'postalZone'     => '12222',
                'country'        => 'SA',
            ],
        ];
    }

    if (!empty($opts['hasDelivery']) || $opts['type'] === 'standard') {
        $invoice['delivery'] = [
            'actualDeliveryDate' => $now->format('Y-m-d'),
        ];
    }

    return $invoice;
}
