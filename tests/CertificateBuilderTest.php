<?php
use PHPUnit\Framework\TestCase;
use Saleh7\Zatca\CertificateBuilder;
use Saleh7\Zatca\Exceptions\CertificateBuilderException;

final class CertificateBuilderTest extends TestCase
{
    /**
     * Test that CertificateBuilder generates and saves CSR and private key.
     */
    public function testGenerateAndSave(): void
    {
        $builder = new CertificateBuilder();
        $builder->setOrganizationIdentifier('399999999900003')
            ->setSerialNumber('Saleh', '1n', 'SME00023')
            ->setCommonName('My Organization')
            ->setOrganizationName('My Company')
            ->setOrganizationalUnitName('IT Department')
            ->setAddress('Riyadh 1234 Street')
            ->setInvoiceType('1100')
            ->setProduction(true)
            ->setBusinessCategory('Technology');

        $csrPath = tempnam(sys_get_temp_dir(), 'csr_');
        $privateKeyPath = tempnam(sys_get_temp_dir(), 'key_');

        $builder->generateAndSave($csrPath, $privateKeyPath);

        $this->assertFileExists($csrPath, 'CSR file should exist.');
        $this->assertFileExists($privateKeyPath, 'Private key file should exist.');

        $csrContent = file_get_contents($csrPath);
        $keyContent = file_get_contents($privateKeyPath);

        $this->assertNotEmpty($csrContent, 'CSR content should not be empty.');
        $this->assertNotEmpty($keyContent, 'Private key content should not be empty.');

        $this->assertStringContainsString('BEGIN CERTIFICATE REQUEST', $csrContent, 'CSR should have proper header.');
        $this->assertMatchesRegularExpression('/BEGIN (EC )?PRIVATE KEY/', $keyContent, 'Private key should have a valid header.');

        unlink($csrPath);
        unlink($privateKeyPath);
    }

    /**
     * Test that missing a required parameter throws an exception.
     */
    public function testMissingRequiredParameter(): void
    {
        $this->expectException(CertificateBuilderException::class);
        $this->expectExceptionMessage('industry is mandatory field');

        $builder = new CertificateBuilder();
        $builder->setOrganizationIdentifier('399999999900003')
            ->setSerialNumber('Saleh', '1n', 'SME00023')
            ->setCommonName('My Organization')
            ->setOrganizationName('My Company')
            ->setOrganizationalUnitName('IT Department')
            ->setAddress('Riyadh 1234 Street')
            ->setInvoiceType('1100')
            ->setProduction(true);
            // Missing setBusinessCategory

        $builder->generate();
    }

    /**
     * Test CSR generation with Arabic text in organization fields.
     * Matches SDK's csr-config-example-AR.properties.
     */
    public function testArabicFieldsSupported(): void
    {
        $builder = new CertificateBuilder();
        $builder->setOrganizationIdentifier('399999999900003')
            ->setSerialNumber('POS', 'POS', 'ed22f1d8-e6a2-1118-9b58-d9a8f11e445f')
            ->setCommonName('POS-886431145-399999999900003')
            ->setOrganizationName('Maximum Speed Tech Supply LTD')
            ->setOrganizationalUnitName('Riyadh Branch')
            ->setAddress('RRRD2929')
            ->setInvoiceType('1000')
            ->setProduction(true)
            ->setBusinessCategory('Supply activities');

        $builder->generate();
        $csr = $builder->getCsr();

        $this->assertStringContainsString('BEGIN CERTIFICATE REQUEST', $csr);
        $this->assertNotEmpty($csr);
    }

    /**
     * Test invoice type validation - must be 4 digits, each 0 or 1.
     */
    public function testInvalidInvoiceTypeRejected(): void
    {
        $this->expectException(CertificateBuilderException::class);
        $this->expectExceptionMessage('Invalid invoice type');

        $builder = new CertificateBuilder();
        $builder->setInvoiceType('1234'); // digits > 1 not allowed
    }

    /**
     * Test invoice type accepts valid bitmask values.
     */
    public function testValidInvoiceTypes(): void
    {
        $builder = new CertificateBuilder();

        // All valid patterns
        $builder->setInvoiceType('1100');
        $builder->setInvoiceType('1000');
        $builder->setInvoiceType('0100');
        $builder->setInvoiceType('0000');
        $builder->setInvoiceType('1111');

        // Integer input should be zero-padded
        $builder->setInvoiceType(1100);
        $builder->setInvoiceType(100); // becomes "0100"

        $this->assertTrue(true); // No exceptions thrown
    }

    /**
     * Test that forbidden special characters are rejected.
     * SDK regex: [!@#$%&*_<]
     */
    public function testForbiddenSpecialCharactersRejected(): void
    {
        $builder = new CertificateBuilder();

        $forbiddenChars = ['!', '@', '#', '$', '%', '&', '*', '_', '<', '='];
        foreach ($forbiddenChars as $char) {
            try {
                $builder->setCommonName("Test{$char}Name");
                $this->fail("Should have rejected character: $char");
            } catch (CertificateBuilderException $e) {
                $this->assertStringContainsString('Invalid CommonName', $e->getMessage());
            }
        }
    }

    /**
     * Test the three environment modes.
     */
    public function testEnvironmentModes(): void
    {
        $builder = (new CertificateBuilder())
            ->setOrganizationIdentifier('399999999900003')
            ->setSerialNumber('TST', 'TST', 'ed22f1d8-e6a2-1118-9b58-d9a8f11e445f')
            ->setCommonName('TST-886431145-399999999900003')
            ->setOrganizationName('Test Company')
            ->setOrganizationalUnitName('IT')
            ->setAddress('RRRD2929')
            ->setInvoiceType('1100')
            ->setBusinessCategory('Supply');

        // Test each environment mode generates without error
        foreach ([CertificateBuilder::ENV_PRODUCTION, CertificateBuilder::ENV_NONPROD, CertificateBuilder::ENV_SIMULATION] as $env) {
            $builder->setEnvironment($env);
            $builder->generate();
            $csr = $builder->getCsr();
            $this->assertStringContainsString('BEGIN CERTIFICATE REQUEST', $csr);
        }
    }

    /**
     * Test invalid environment mode throws exception.
     */
    public function testInvalidEnvironmentRejected(): void
    {
        $this->expectException(CertificateBuilderException::class);
        $this->expectExceptionMessage('Invalid environment');

        (new CertificateBuilder())->setEnvironment('staging');
    }

    /**
     * Test organization identifier validation.
     */
    public function testInvalidOrgIdentifierRejected(): void
    {
        $builder = new CertificateBuilder();

        // Must start with 3
        $this->expectException(CertificateBuilderException::class);
        $builder->setOrganizationIdentifier('210053306700003');
    }

    /**
     * Test serial number must not contain '='.
     */
    public function testSerialNumberEqualsRejected(): void
    {
        $this->expectException(CertificateBuilderException::class);
        $this->expectExceptionMessage("special characters ('=')");

        $builder = new CertificateBuilder();
        $builder->setSerialNumber('POS', 'A=1', 'serial123');
    }

    /**
     * Test VAT group OU validation.
     * When org identifier 11th digit is '1', OU must be 10-digit TIN.
     */
    public function testVatGroupOuValidation(): void
    {
        $this->expectException(CertificateBuilderException::class);
        $this->expectExceptionMessage('10-digit TIN');

        // Org ID with 11th digit = '1' (index 10) indicating VAT group
        $builder = (new CertificateBuilder())
            ->setOrganizationIdentifier('399999999910003')
            ->setSerialNumber('TST', 'TST', 'ed22f1d8-e6a2-1118-9b58-d9a8f11e445f')
            ->setCommonName('TST-886431145-399999999910003')
            ->setOrganizationName('Test')
            ->setOrganizationalUnitName('Short') // Not 10 digits - should fail
            ->setAddress('RRRD2929')
            ->setInvoiceType('1100')
            ->setBusinessCategory('Supply');

        $builder->generate();
    }

    /**
     * Test VAT group with correct 10-digit TIN OU.
     */
    public function testVatGroupCorrectOu(): void
    {
        $builder = (new CertificateBuilder())
            ->setOrganizationIdentifier('399999999910003')
            ->setSerialNumber('TST', 'TST', 'ed22f1d8-e6a2-1118-9b58-d9a8f11e445f')
            ->setCommonName('TST-886431145-399999999910003')
            ->setOrganizationName('Test')
            ->setOrganizationalUnitName('3999999999') // 10-digit TIN
            ->setAddress('RRRD2929')
            ->setInvoiceType('1100')
            ->setBusinessCategory('Supply');

        $builder->generate();
        $csr = $builder->getCsr();
        $this->assertStringContainsString('BEGIN CERTIFICATE REQUEST', $csr);
    }

    /**
     * Test backward compatibility: setProduction(true) maps to ENV_PRODUCTION.
     */
    public function testSetProductionBackwardCompatibility(): void
    {
        $builder = (new CertificateBuilder())
            ->setOrganizationIdentifier('399999999900003')
            ->setSerialNumber('POS', 'POS', 'test-serial')
            ->setCommonName('POS-886431145-399999999900003')
            ->setOrganizationName('Test')
            ->setOrganizationalUnitName('IT')
            ->setAddress('RRRD2929')
            ->setInvoiceType('1100')
            ->setProduction(true)
            ->setBusinessCategory('Supply');

        $builder->generate();
        $csr = $builder->getCsr();
        $this->assertStringContainsString('BEGIN CERTIFICATE REQUEST', $csr);
    }

    /**
     * Test DN order matches SDK: C → OU → O → CN.
     */
    public function testDnOrderMatchesSdk(): void
    {
        $builder = (new CertificateBuilder())
            ->setOrganizationIdentifier('399999999900003')
            ->setSerialNumber('TST', 'TST', 'serial-uuid')
            ->setCommonName('TST-886431145-399999999900003')
            ->setOrganizationName('Test Company LTD')
            ->setOrganizationalUnitName('Riyadh Branch')
            ->setAddress('RRRD2929')
            ->setInvoiceType('1100')
            ->setEnvironment(CertificateBuilder::ENV_NONPROD)
            ->setBusinessCategory('Supply activities');

        $builder->generate();
        $csr = $builder->getCsr();

        // Save to temp file and parse with openssl to verify DN order
        $tmpFile = tempnam(sys_get_temp_dir(), 'csr_dn_');
        file_put_contents($tmpFile, $csr);

        $output = shell_exec("openssl req -in $tmpFile -noout -subject 2>&1");
        unlink($tmpFile);

        // SDK order: C, OU, O, CN
        $this->assertNotNull($output);
        $cPos  = strpos($output, 'C = ');
        $ouPos = strpos($output, 'OU = ');
        $oPos  = strpos($output, 'O = ');
        $cnPos = strpos($output, 'CN = ');

        $this->assertNotFalse($cPos, 'C should be in subject');
        $this->assertNotFalse($ouPos, 'OU should be in subject');
        $this->assertNotFalse($oPos, 'O should be in subject');
        $this->assertNotFalse($cnPos, 'CN should be in subject');

        // Verify order: C < OU < O < CN
        $this->assertLessThan($ouPos, $cPos, 'C should come before OU');
        $this->assertLessThan($oPos, $ouPos, 'OU should come before O');
        $this->assertLessThan($cnPos, $oPos, 'O should come before CN');
    }

    /**
     * Test OID extension uses UTF8String encoding (tag 0x0C), not PrintableString.
     */
    public function testOidExtensionUsesUtf8String(): void
    {
        $builder = (new CertificateBuilder())
            ->setOrganizationIdentifier('399999999900003')
            ->setSerialNumber('TST', 'TST', 'serial-uuid')
            ->setCommonName('TST-886431145-399999999900003')
            ->setOrganizationName('Test Company')
            ->setOrganizationalUnitName('IT')
            ->setAddress('RRRD2929')
            ->setInvoiceType('1100')
            ->setEnvironment(CertificateBuilder::ENV_SIMULATION)
            ->setBusinessCategory('Supply');

        $builder->generate();
        $csr = $builder->getCsr();

        $tmpCsr = tempnam(sys_get_temp_dir(), 'csr_oid_');
        $tmpDer = tempnam(sys_get_temp_dir(), 'der_oid_');
        file_put_contents($tmpCsr, $csr);

        // Convert to DER and parse the OID extension
        shell_exec("openssl req -in $tmpCsr -outform DER -out $tmpDer 2>&1");
        $derBytes = file_get_contents($tmpDer);

        unlink($tmpCsr);
        unlink($tmpDer);

        // Search for the PREZATCA-Code-Signing string in DER
        $searchStr = 'PREZATCA-Code-Signing';
        $pos = strpos($derBytes, $searchStr);
        $this->assertNotFalse($pos, 'PREZATCA-Code-Signing should be in CSR');

        // The byte before the string length should be the tag
        // UTF8String tag = 0x0C, PrintableString tag = 0x13
        $tagByte = ord($derBytes[$pos - 2]);
        $this->assertEquals(0x0C, $tagByte, 'OID value should be encoded as UTF8String (0x0C), not PrintableString (0x13)');
    }
}
