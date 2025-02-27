<?php
use PHPUnit\Framework\TestCase;
use Saleh7\Zatca\Mappers\InvoiceMapper;
use Saleh7\Zatca\Invoice;

/**
 * Class InvoiceMapperTest
 *
 * This class tests the functionality of the InvoiceMapper class,
 * ensuring that invoice data is correctly mapped to an Invoice object.
 */
class InvoiceMapperTest extends TestCase
{
    /**
     * @var InvoiceMapper
     */
    private InvoiceMapper $mapper;

    /**
     * Setup before each test.
     */
    protected function setUp(): void
    {
        $this->mapper = new InvoiceMapper();
    }

    /**
     * Test that valid invoice data is correctly mapped to an Invoice object.
     *
     * This test uses a sample invoice array with all required fields and
     * verifies that the mapping returns an instance of Invoice.
     */
    public function testMapToInvoiceWithValidData(): void
    {
        $invoiceData = [
            'uuid' => '3cf5ee18-ee25-44ea-a444-2c37ba7f28be',
            'id' => 'SME00023',
            'issueDate' => '2024-09-07 17:41:08',
            'issueTime' => '2024-09-07 17:41:08',
            'currencyCode' => 'SAR',
            'taxCurrencyCode' => 'SAR',
            'invoiceType' => [
                'invoice' => 'standard', // "standard" or "simplified"
                'type' => 'invoice',
                'isThirdParty' => false,
                'isNominal' => false,
                'isExport' => false,
                'isSummary' => false,
                'isSelfBilled' => false
            ],
            'additionalDocuments' => [
                [
                    'id' => 'ICV',
                    'uuid' => '10'
                ],
                [
                    'id' => 'PIH',
                    'attachment' => [
                        'content' => 'dummyBase64Content',
                        'mimeCode' => 'base64',
                        'mimeType' => 'text/plain'
                    ]
                ],
                [
                    'id' => 'QR'
                ]
            ],
            'supplier' => [
                'registrationName' => 'Maximum Speed Tech Supply',
                'taxId' => '399999999900003',
                'taxScheme' => [
                    'id' => 'VAT'
                ],
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
                'taxScheme' => [
                    'id' => 'VAT'
                ],
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
                'code' => '10'
            ],
            'delivery' => [
                'actualDeliveryDate' => '2022-09-07',
            ],
            'allowanceCharges' => [
                [
                    'isCharge' => false,
                    'reason' => 'discount',
                    'amount' => 0.00,
                    'taxCategories' => [
                        [
                            'percent' => 15,
                            'taxScheme' => [
                                'id' => 'VAT'
                            ]
                        ]
                    ]
                ]
            ],
            'taxTotal' => [
                'taxAmount' => 0.6,
                'subTotals' => [
                    [
                        'taxableAmount' => 4,
                        'taxAmount' => 0.6,
                        'taxCategory' => [
                            'percent' => 15,
                            'taxScheme' => [
                                'id' => 'VAT'
                            ]
                        ]
                    ]
                ]
            ],
            'legalMonetaryTotal' => [
                'lineExtensionAmount' => 4,
                'taxExclusiveAmount' => 4,
                'taxInclusiveAmount' => 4.60,
                'prepaidAmount' => 0,
                'payableAmount' => 4.60,
                'allowanceTotalAmount' => 0
            ],
            'invoiceLines' => [
                [
                    'id' => 1,
                    'unitCode' => 'PCE',
                    'quantity' => 2,
                    'lineExtensionAmount' => 4,
                    'item' => [
                        'name' => 'Product',
                        'classifiedTaxCategory' => [
                            [
                                'percent' => 15,
                                'taxScheme' => [
                                    'id' => 'VAT'
                                ]
                            ]
                        ],
                    ],
                    'price' => [
                        'amount' => 2,
                        'unitCode' => 'UNIT',
                        'allowanceCharges' => [
                            [
                                'isCharge' => true,
                                'reason' => 'discount',
                                'amount' => 0.00
                            ]
                        ]
                    ],
                    'taxTotal' => [
                        'taxAmount' => 0.60,
                        'roundingAmount' => 4.60
                    ]
                ]
            ]
        ];

        // Map the invoice data to an Invoice object
        $invoice = $this->mapper->mapToInvoice($invoiceData);

        // Assert that the returned object is an instance of Invoice
        $this->assertInstanceOf(Invoice::class, $invoice);
        
        // Additional assertions can be added to validate specific properties
    }

    /**
     * Test that mapping with invalid (empty) data throws an exception.
     *
     * This test verifies that an InvalidArgumentException is thrown when required data is missing.
     */
    public function testMapToInvoiceWithInvalidDataThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->mapper->mapToInvoice([]); // Passing empty data should trigger validation error
    }
}
