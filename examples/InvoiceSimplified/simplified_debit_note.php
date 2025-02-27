<?php
require_once __DIR__ . '/../../vendor/autoload.php';
use Saleh7\Zatca\Mappers\InvoiceMapper;
use Saleh7\Zatca\GeneratorInvoice;
use Saleh7\Zatca\Helpers\Certificate;
use Saleh7\Zatca\InvoiceSigner;

// Sample data (can be from JSON, array, database, etc.)
$invoiceData = [
    'uuid' => 'ec65d239-c793-452f-8e8c-509dbd54d2a9',
    'id' => 'SME00099',
    'issueDate' => '2024-09-07 17:41:08',
    'issueTime' => '2024-09-07 17:41:08',
    'currencyCode' => 'SAR',
    'taxCurrencyCode' => 'SAR',
    'invoiceType' => [
        'invoice' => 'simplified',
        'type' => 'credit',
        'isThirdParty' => true,
        'isNominal' => true,
        'isExport' => false,
        'isSummary' => true,
        'isSelfBilled' => false
    ],
    'billingReferences' => [
        [
            'id' => 'SME00098'
        ]
    ],
    'additionalDocuments' => [
        [
            'id' => 'ICV',
            'uuid' => '10'
        ],
        [
            'id' => 'PIH',
            'attachment' => [
                'content' => 'NWZlY2ViNjZmZmM4NmYzOGQ5NTI3ODZjNmQ2OTZjNzljMmRiYzIzOWRkNGU5MWI0NjcyOWQ3M2EyN2ZiNTdlOQ==',
            ]
        ],
    ],
    'supplier' => [
        'registrationName' => 'Latency',
        'taxId' => '399999999900003',
        'identificationId' => '1010010000',
        'identificationType' => 'CRN',
        'address' => [
            'street' => 'Prince Sultan',
            'buildingNumber' => '2322',
            'subdivision' => 'Al-Murabba',
            'city' => 'Riyadh',
            'postalZone' => '23333',
            'country' => 'SA'
        ]
    ],
    'customer' => [
        'registrationName' => 'Fatoora Samples',
        'taxId' => '399999999800003',
        'address' => [
            'street' => 'Salah Al-Din',
            'buildingNumber' => '1111',
            'subdivision' => 'Al-Murooj',
            'city' => 'Riyadh',
            'postalZone' => '12222',
            'country' => 'SA'
        ]
    ],
    'paymentMeans' => [
        'code' => '42',
        'note' => 'Returned items'
    ],
    'delivery' => [
        'actualDeliveryDate' => '2025-02-05',
        'latestDeliveryDate' => '2025-02-09'
    ],
    'taxTotal' => [
        'taxAmount' => 0,
        'subTotals' => [
            [
                'taxableAmount' => 250,
                'taxAmount' => 0,
                'taxCategory' => [
                    'percent' => 0,
                    'reasonCode' => 'VATEX-SA-HEA ',
                    'reason' => 'Private healthcare to citizen',
                    'taxScheme' => [
                        'id' => 'VAT'
                    ]
                ]
            ]
        ]
    ],
    'legalMonetaryTotal' => [
        'lineExtensionAmount' => 250,
        'taxExclusiveAmount' => 250,
        'taxInclusiveAmount' => 250,
        'payableAmount' => 250,
    ],
    'invoiceLines' => [
        [
            'id' => 1,
            'unitCode' => 'PCE',
            'quantity' => 1,
            'lineExtensionAmount' => 250,
            'item' => [
                'name' => 'Surgery',
                'classifiedTaxCategory' => [
                    [
                        'percent' => 0,
                        'taxScheme' => [
                            'id' => 'VAT'
                        ]
                    ]
                ],
            ],
            'price' => [
                'amount' => 250,
                'unitCode' => 'UNIT',
            ],
            'taxTotal' => [
                'taxAmount' => 0,
                'roundingAmount' => 250
            ]
        ]
    ]
];

// Map the data to an Invoice object
$invoiceMapper = new InvoiceMapper();
$invoice = $invoiceMapper->mapToInvoice($invoiceData);

// Generate the invoice XML
$outputXML = GeneratorInvoice::invoice($invoice)->saveXMLFile('Simplified_Debit_Note.xml');
echo "Simplified Invoice Generated Successfully\n";

// get invoice.xml ..
$xmlInvoice = file_get_contents('output/Simplified_Debit_Note.xml');
$certificate = (new Certificate(
    'MIID3jCCA4SgAwIBAgITEQAAOAPF90Ajs/xcXwABAAA4AzAKBggqhkjOPQQDAjBiMRUwEwYKCZImiZPyLGQBGRYFbG9jYWwxEzARBgoJkiaJk/IsZAEZFgNnb3YxFzAVBgoJkiaJk/IsZAEZFgdleHRnYXp0MRswGQYDVQQDExJQUlpFSU5WT0lDRVNDQTQtQ0EwHhcNMjQwMTExMDkxOTMwWhcNMjkwMTA5MDkxOTMwWjB1MQswCQYDVQQGEwJTQTEmMCQGA1UEChMdTWF4aW11bSBTcGVlZCBUZWNoIFN1cHBseSBMVEQxFjAUBgNVBAsTDVJpeWFkaCBCcmFuY2gxJjAkBgNVBAMTHVRTVC04ODY0MzExNDUtMzk5OTk5OTk5OTAwMDAzMFYwEAYHKoZIzj0CAQYFK4EEAAoDQgAEoWCKa0Sa9FIErTOv0uAkC1VIKXxU9nPpx2vlf4yhMejy8c02XJblDq7tPydo8mq0ahOMmNo8gwni7Xt1KT9UeKOCAgcwggIDMIGtBgNVHREEgaUwgaKkgZ8wgZwxOzA5BgNVBAQMMjEtVFNUfDItVFNUfDMtZWQyMmYxZDgtZTZhMi0xMTE4LTliNTgtZDlhOGYxMWU0NDVmMR8wHQYKCZImiZPyLGQBAQwPMzk5OTk5OTk5OTAwMDAzMQ0wCwYDVQQMDAQxMTAwMREwDwYDVQQaDAhSUlJEMjkyOTEaMBgGA1UEDwwRU3VwcGx5IGFjdGl2aXRpZXMwHQYDVR0OBBYEFEX+YvmmtnYoDf9BGbKo7ocTKYK1MB8GA1UdIwQYMBaAFJvKqqLtmqwskIFzVvpP2PxT+9NnMHsGCCsGAQUFBwEBBG8wbTBrBggrBgEFBQcwAoZfaHR0cDovL2FpYTQuemF0Y2EuZ292LnNhL0NlcnRFbnJvbGwvUFJaRUludm9pY2VTQ0E0LmV4dGdhenQuZ292LmxvY2FsX1BSWkVJTlZPSUNFU0NBNC1DQSgxKS5jcnQwDgYDVR0PAQH/BAQDAgeAMDwGCSsGAQQBgjcVBwQvMC0GJSsGAQQBgjcVCIGGqB2E0PsShu2dJIfO+xnTwFVmh/qlZYXZhD4CAWQCARIwHQYDVR0lBBYwFAYIKwYBBQUHAwMGCCsGAQUFBwMCMCcGCSsGAQQBgjcVCgQaMBgwCgYIKwYBBQUHAwMwCgYIKwYBBQUHAwIwCgYIKoZIzj0EAwIDSAAwRQIhALE/ichmnWXCUKUbca3yci8oqwaLvFdHVjQrveI9uqAbAiA9hC4M8jgMBADPSzmd2uiPJA6gKR3LE03U75eqbC/rXA==',
    'MHQCAQEEIL14JV+5nr/sE8Sppaf2IySovrhVBtt8+yz+g4NRKyz8oAcGBSuBBAAKoUQDQgAEoWCKa0Sa9FIErTOv0uAkC1VIKXxU9nPpx2vlf4yhMejy8c02XJblDq7tPydo8mq0ahOMmNo8gwni7Xt1KT9UeA==',
    'secret' 
));

// sign the invoice XML with the certificate
InvoiceSigner::signInvoice($xmlInvoice, $certificate)->saveXMLFile('Simplified_Debit_Note_Signed.xml');
echo "Simplified Invoice Signed Successfully\n";