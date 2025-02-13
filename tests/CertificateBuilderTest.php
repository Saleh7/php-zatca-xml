<?php
use PHPUnit\Framework\TestCase;
use Saleh7\Zatca\CertificateBuilder;
use Saleh7\Zatca\CertificateBuilderException;

final class CertificateBuilderTest extends TestCase
{
    /**
     * Test that CertificateBuilder generates and saves CSR and private key.
     */
    public function testGenerateAndSave(): void
    {
        // Create CertificateBuilder and set required parameters.
        $builder = new CertificateBuilder();
        $builder->setOrganizationIdentifier('312345678901233')
            ->setSerialNumber('Saleh', '1n', 'SME00023')
            ->setCommonName('My Organization')
            ->setOrganizationName('My Company')
            ->setOrganizationalUnitName('IT Department')
            ->setAddress('Riyadh 1234 Street')
            ->setInvoiceType(1100)
            ->setProduction(true)
            ->setBusinessCategory('Technology');

        // Create temporary files for CSR and private key.
        $csrPath = tempnam(sys_get_temp_dir(), 'csr_');
        $privateKeyPath = tempnam(sys_get_temp_dir(), 'key_');

        // Generate and save CSR and private key.
        $builder->generateAndSave($csrPath, $privateKeyPath);

        // Assert files exist.
        $this->assertFileExists($csrPath, 'CSR file should exist.');
        $this->assertFileExists($privateKeyPath, 'Private key file should exist.');

        // Read file contents.
        $csrContent = file_get_contents($csrPath);
        $keyContent = file_get_contents($privateKeyPath);

        $this->assertNotEmpty($csrContent, 'CSR content should not be empty.');
        $this->assertNotEmpty($keyContent, 'Private key content should not be empty.');

        // Check that CSR content has the correct header.
        $this->assertStringContainsString('BEGIN CERTIFICATE REQUEST', $csrContent, 'CSR should have proper header.');

        // Check that private key content has a valid header (could be EC PRIVATE KEY or PRIVATE KEY).
        $this->assertMatchesRegularExpression('/BEGIN (EC )?PRIVATE KEY/', $keyContent, 'Private key should have a valid header.');

        // Clean up temporary files.
        unlink($csrPath);
        unlink($privateKeyPath);
    }

    /**
     * Test that missing a required parameter throws an exception.
     */
    public function testMissingRequiredParameter(): void
    {
        $this->expectException(CertificateBuilderException::class);
        $this->expectExceptionMessage('Missing required parameter: businessCategory');

        // Create CertificateBuilder without setting businessCategory.
        $builder = new CertificateBuilder();
        $builder->setOrganizationIdentifier('312345678901233')
            ->setSerialNumber('Saleh', '1n', 'SME00023')
            ->setCommonName('My Organization')
            ->setOrganizationName('My Company')
            ->setOrganizationalUnitName('IT Department')
            ->setAddress('Riyadh 1234 Street')
            ->setInvoiceType(1100)
            ->setProduction(true);
            // Missing setBusinessCategory

        // Calling generate() should throw an exception.
        $builder->generate();
    }
}
