<?php
use PHPUnit\Framework\TestCase;
use Saleh7\Zatca\Mappers\Validators\InvoiceValidator;

/**
 * Class InvoiceValidatorTest
 *
 * This class tests the functionality of the InvoiceValidator class,
 * ensuring that the required invoice fields are validated correctly.
 */
class InvoiceValidatorTest extends TestCase
{
    /**
     * @var InvoiceValidator
     */
    private InvoiceValidator $validator;

    /**
     * Setup before each test.
     */
    protected function setUp(): void
    {
        $this->validator = new InvoiceValidator();
    }

    /**
     * Test that valid invoice data passes validation.
     *
     * This test uses a sample invoice data array with all required fields
     * and expects that no exception is thrown during validation.
     */
    public function testValidInvoiceData(): void
    {
        $data = [
            'uuid'            => '123e4567-e89b-12d3-a456-426614174000',
            'id'              => 'INV-001',
            'issueDate'       => '2024-09-07',
            'currencyCode'    => 'SAR',
            'taxCurrencyCode' => 'SAR',
            'invoiceType'     => [
                'invoice' => 'standard',
                'type'    => 'invoice'
            ],
            'supplier' => [
                'registrationName' => 'Supplier Inc.',
                'taxId'            => '1234567890',
                'taxScheme'        => ['id' => 'VAT'],
                'address'          => [
                    'street'         => 'Main St',
                    'buildingNumber' => '123',
                    'city'           => 'Riyadh',
                    'postalZone'     => '12345',
                    'country'        => 'SA'
                ]
            ],
            'customer' => [
                'registrationName' => 'Customer LLC',
                'taxId'            => '0987654321',
                'taxScheme'        => ['id' => 'VAT'],
                'address'          => [
                    'street'         => 'Second St',
                    'buildingNumber' => '456',
                    'city'           => 'Jeddah',
                    'postalZone'     => '54321',
                    'country'        => 'SA'
                ]
            ],
            'legalMonetaryTotal' => [
                'lineExtensionAmount' => 100,
                'taxExclusiveAmount'  => 100,
                'taxInclusiveAmount'  => 115,
                'payableAmount'       => 115
            ],
            'invoiceLines' => [
                [
                    'id'                => 1,
                    'unitCode'          => 'PCE',
                    'quantity'          => 2,
                    'lineExtensionAmount'=> 200, // 100 * 2 = 200
                    'item' => [
                        'name'        => 'Product A',
                        'taxPercent'  => 15,
                        'taxScheme'   => ['id' => 'VAT']
                    ],
                    'price' => [
                        'amount'      => 100,
                        'unitCode'    => 'PCE'
                    ],
                    'taxTotal' => [
                        'taxAmount'     => 30,
                        'roundingAmount'=> 230  // 200 + 30 = 230
                    ]
                ]
            ]
        ];

        // Expect no exception during validation.
        $this->validator->validate($data);
        $this->assertTrue(true); // Test passes if no exception is thrown.
    }

    /**
     * Test that missing a required field (e.g., 'uuid') triggers an exception.
     *
     * This test omits the 'uuid' field and expects an InvalidArgumentException.
     */
    public function testMissingRequiredFieldThrowsException(): void
    {
        $data = [
            // 'uuid' is intentionally omitted
            'id'              => 'INV-002',
            'issueDate'       => '2024-09-07',
            'currencyCode'    => 'SAR',
            'taxCurrencyCode' => 'SAR',
            'invoiceType'     => [
                'invoice' => 'standard',
                'type'    => 'invoice'
            ],
            'supplier' => [
                'registrationName' => 'Supplier Inc.',
                'taxId'            => '1234567890',
                'taxScheme'        => ['id' => 'VAT'],
                'address'          => [
                    'street'         => 'Main St',
                    'buildingNumber' => '123',
                    'city'           => 'Riyadh',
                    'postalZone'     => '12345',
                    'country'        => 'SA'
                ]
            ],
            'customer' => [
                'registrationName' => 'Customer LLC',
                'taxId'            => '0987654321',
                'taxScheme'        => ['id' => 'VAT'],
                'address'          => [
                    'street'         => 'Second St',
                    'buildingNumber' => '456',
                    'city'           => 'Jeddah',
                    'postalZone'     => '54321',
                    'country'        => 'SA'
                ]
            ],
            'legalMonetaryTotal' => [
                'lineExtensionAmount' => 100,
                'taxExclusiveAmount'  => 100,
                'taxInclusiveAmount'  => 115,
                'payableAmount'       => 115
            ],
            'invoiceLines' => [
                [
                    'id'                => 1,
                    'unitCode'          => 'PCE',
                    'quantity'          => 2,
                    'lineExtensionAmount'=> 200,
                    'item' => [
                        'name'        => 'Product A',
                        'taxPercent'  => 15,
                        'taxScheme'   => ['id' => 'VAT']
                    ],
                    'price' => [
                        'amount'      => 100,
                        'unitCode'    => 'PCE'
                    ],
                    'taxTotal' => [
                        'taxAmount'     => 30,
                        'roundingAmount'=> 230
                    ]
                ]
            ]
        ];

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("The field 'UUID' is required");
        $this->validator->validate($data);
    }
}