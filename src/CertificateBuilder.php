<?php
namespace Saleh7\Zatca;
use Saleh7\Zatca\Exceptions\CertificateBuilderException;
use Saleh7\Zatca\Exceptions\ZatcaStorageException;

/**
 * Class CertificateBuilder
 *
 * Builds a CSR and private key using OpenSSL, fully aligned with ZATCA's official
 * Java SDK (R3.4.8) CSR generation: secp256k1 + SHA256withECDSA, matching DN order,
 * SAN dirName attributes, OID extension encoding, and input validation rules.
 *
 * @see https://zatca.gov.sa/en/E-Invoicing/Introduction/Guidelines/Documents/Fatoora_Portal_User_Manual_English.pdf page 31 ..
 */
class CertificateBuilder {
    /**
     * Certificate template OID values per environment.
     * Maps to OID 1.3.6.1.4.1.311.20.2 (Microsoft Certificate Template Name).
     *
     * SDK reference (CsrGenerationService.generate):
     *   - production:  "ZATCA-Code-Signing"
     *   - nonprod:     "TSTZATCA-Code-Signing"
     *   - simulation:  "PREZATCA-Code-Signing"
     */
    private const OID_PROD       = 'ZATCA-Code-Signing';
    private const OID_NONPROD    = 'TSTZATCA-Code-Signing';
    private const OID_SIMULATION = 'PREZATCA-Code-Signing';

    /**
     * Environment modes matching the SDK's three-tier model.
     */
    public const ENV_PRODUCTION = 'production';
    public const ENV_NONPROD    = 'nonprod';
    public const ENV_SIMULATION = 'simulation';

    /**
     * OpenSSL config template.
     *
     * SDK uses BouncyCastle DisplayText(UTF8String) for the OID extension value.
     * OpenSSL equivalent: ASN1:UTF8String
     *
     * DN order is controlled via the $dn array passed to openssl_csr_new(),
     * matching SDK (BouncyCastle X500NameBuilder): C → OU → O → CN.
     * The [req_dn] section is kept empty; the custom config prevents OpenSSL
     * from injecting default fields (e.g. ST=Some-State).
     */
    private const CONFIG_TEMPLATE = <<<'EOL'
[req]
prompt = no
utf8 = yes
distinguished_name = req_dn

[req_dn]

[v3_req]
1.3.6.1.4.1.311.20.2 = ASN1:UTF8String:%s
subjectAltName = dirName:dir_sect

[dir_sect]
EOL;

    /**
     * Forbidden characters regex matching the SDK's specialCharacterRegex.
     *
     * SDK (application.properties): [!@#$%&*_<]
     * We also block = (SDK validates serial number must not contain '=').
     */
    private const FORBIDDEN_CHARS_REGEX = '/[!@#$%&*_<=]/';

    /** @var string */
    private string $organizationIdentifier = '';
    /** @var string */
    private string $serialNumber = '';
    /** @var string */
    private string $commonName = '';
    /** @var string */
    private string $country = 'SA';
    /** @var string */
    private string $organizationName = '';
    /** @var string */
    private string $organizationalUnitName = '';
    /** @var string */
    private string $address = '';
    /** @var string 4-char bitmask: each digit 0 or 1 */
    private string $invoiceType = '1100';
    /** @var string Environment mode */
    private string $environment = self::ENV_SIMULATION;
    /** @var string */
    private string $businessCategory = '';

    /**
     * In PHP 8.0+, openssl_pkey_new returns an OpenSSLAsymmetricKey object.
     * In earlier versions, it returns a resource.
     *
     * @var resource|object|null
     */
    private $privateKey = null;

    /**
     * The CSR resource/object.
     *
     * @var resource|object|null
     */
    private $csr = null;

    /**
     * Set organization identifier (15 digits, starts and ends with 3).
     *
     * SDK validation: length == 15, first char == '3', last char == '3'.
     * Additional check for the 11th digit (index 10) == '1' triggers VAT group
     * OU validation (handled in validateParameters).
     */
    public function setOrganizationIdentifier(string $identifier): self {
        $identifier = trim($identifier);
        if (!preg_match('/^3\d{13}3$/', $identifier)) {
            throw new CertificateBuilderException(
                'Invalid organization identifier, please provide a valid 15 digit of your VAT number starting and ending with 3.'
            );
        }
        $this->organizationIdentifier = $identifier;
        return $this;
    }

    /**
     * Set serial number using solution name, model, and device serial.
     *
     * SDK format: "1-{solutionName}|2-{model}|3-{serialNumber}"
     * SDK regex validation: 1-(.+)\|2-(.+)\|3-(.+)
     * SDK also validates: must not contain '='
     *
     * @param string $solutionName The solution provider name (e.g. "POS", "ERP")
     * @param string $model        The model of the unit (e.g. "POS", "A1")
     * @param string $serialNumber Unique device serial (UUID recommended, e.g. "ed22f1d8-e6a2-1118-9b58-d9a8f11e445f")
     */
    public function setSerialNumber(string $solutionName, string $model, string $serialNumber): self {
        $solutionName = trim($solutionName);
        $model        = trim($model);
        $serialNumber = trim($serialNumber);

        if ($solutionName === '' || $model === '' || $serialNumber === '') {
            throw new CertificateBuilderException('Serial number components (solutionName, model, serialNumber) must not be empty.');
        }

        $formatted = sprintf('1-%s|2-%s|3-%s', $solutionName, $model, $serialNumber);

        // SDK validation: must not contain '='
        if (str_contains($formatted, '=')) {
            throw new CertificateBuilderException(
                "Invalid serial number, The serial number should only contain alphanumeric characters, and special characters ('=') are not allowed."
            );
        }

        $this->serialNumber = $formatted;
        return $this;
    }

    /**
     * Set common name.
     *
     * SDK validates against special characters: [!@#$%&*_<]
     */
    public function setCommonName(string $name): self {
        $name = trim($name);
        $this->validateFieldChars($name, 'CommonName');
        $this->commonName = $name;
        return $this;
    }

    /**
     * Set 2- or 3-character country code.
     *
     * SDK validation: length >= 2 and length <= 3.
     */
    public function setCountryName(string $country): self {
        $country = trim($country);
        $len = strlen($country);
        if ($len < 2 || $len > 3) {
            throw new CertificateBuilderException('Invalid country code name, please provide a valid country code name (2-3 characters).');
        }
        $this->country = strtoupper($country);
        return $this;
    }

    /**
     * Set organization name.
     *
     * Supports Arabic and English text. SDK validates against special characters.
     */
    public function setOrganizationName(string $name): self {
        $name = trim($name);
        $this->validateFieldChars($name, 'OrganizationName');
        $this->organizationName = $name;
        return $this;
    }

    /**
     * Set organizational unit name.
     *
     * For VAT groups (org identifier 11th digit == '1'), this must be a 10-digit TIN.
     * SDK validates against special characters.
     */
    public function setOrganizationalUnitName(string $name): self {
        $name = trim($name);
        $this->validateFieldChars($name, 'OrganizationUnitName');
        $this->organizationalUnitName = $name;
        return $this;
    }

    /**
     * Set address (location).
     *
     * SDK validates against special characters.
     */
    public function setAddress(string $address): self {
        $address = trim($address);
        $this->validateFieldChars($address, 'Location');
        $this->address = $address;
        return $this;
    }

    /**
     * Set invoice type as a 4-digit bitmask.
     *
     * SDK validation: exactly 4 characters, each digit 0 or 1.
     * Bit positions: [Standard Invoice, Simplified, future use, future use]
     * Example: 1100 = Standard + Simplified, 1000 = Standard only, 0100 = Simplified only.
     *
     * @param string|int $type Invoice type bitmask (e.g. "1100", "1000", or int 1100)
     */
    public function setInvoiceType(string|int $type): self {
        $typeStr = str_pad((string)$type, 4, '0', STR_PAD_LEFT);
        if (!preg_match('/^[0-1]{4}$/', $typeStr)) {
            throw new CertificateBuilderException(
                'Invalid invoice type, please provide a valid invoice type (4 digits, each 0 or 1). '
                . 'Example: "1100" for Standard+Simplified.'
            );
        }
        $this->invoiceType = $typeStr;
        return $this;
    }

    /**
     * Set production mode. true = Production, false = Simulation/Testing.
     *
     * @deprecated Use setEnvironment() for full three-tier control matching SDK behavior.
     */
    public function setProduction(bool $production): self {
        $this->environment = $production ? self::ENV_PRODUCTION : self::ENV_SIMULATION;
        return $this;
    }

    /**
     * Set the environment mode matching SDK's three-tier model.
     *
     * @param string $environment One of: ENV_PRODUCTION, ENV_NONPROD, ENV_SIMULATION
     */
    public function setEnvironment(string $environment): self {
        if (!in_array($environment, [self::ENV_PRODUCTION, self::ENV_NONPROD, self::ENV_SIMULATION], true)) {
            throw new CertificateBuilderException(
                "Invalid environment '$environment'. Use CertificateBuilder::ENV_PRODUCTION, ENV_NONPROD, or ENV_SIMULATION."
            );
        }
        $this->environment = $environment;
        return $this;
    }

    /**
     * Set business category (industry).
     *
     * Supports Arabic and English text. SDK validates against special characters.
     */
    public function setBusinessCategory(string $category): self {
        $category = trim($category);
        $this->validateFieldChars($category, 'Industry');
        $this->businessCategory = $category;
        return $this;
    }

    /**
     * Generate CSR and private key.
     */
    public function generate(): void {
        $this->validateParameters();
        $config = $this->createOpenSslConfig();
        try {
            $this->generateKeys($config);
        } finally {
            if (isset($config['config']) && file_exists($config['config'])) {
                unlink($config['config']);
            }
        }
    }

    /**
     * Generate and save CSR and key to files.
     *
     * @param string $csrPath Path to save the CSR (default: certificate.csr)
     * @param string $privateKeyPath Path to save the private key (default: private.pem)
     * @throws CertificateBuilderException
     */
    public function generateAndSave(string $csrPath = 'certificate.csr', string $privateKeyPath = 'private.pem'): void {
        $this->generate();

        $csrContent = $this->getCsr();

        try {
            (new Storage())->put($csrPath, $csrContent);
        } catch (ZatcaStorageException $e) {
            throw new CertificateBuilderException("Failed to save CSR.", $e->getContext());
        }

        $this->savePrivateKey($privateKeyPath);
    }

    /**
     * Get CSR as a string (PEM format).
     */
    public function getCsr(): string {
        if (!$this->csr) {
            throw new CertificateBuilderException('CSR not generated. Call generate() first.');
        }
        if (!openssl_csr_export($this->csr, $csr)) {
            throw new CertificateBuilderException('CSR export failed: ' . $this->getOpenSslErrors());
        }
        return $csr;
    }

    /**
     * Save private key to a file (PEM format).
     */
    public function savePrivateKey(string $path): void {
        if (!openssl_pkey_export_to_file($this->privateKey, $path)) {
            throw new CertificateBuilderException('Private key export failed: ' . $this->getOpenSslErrors());
        }
    }

    /**
     * Validate required parameters before CSR generation.
     *
     * Mirrors SDK's validateCsrConfigInputFile logic including VAT group OU check.
     */
    private function validateParameters(): void {
        $requiredFields = [
            'commonName'               => 'common name',
            'serialNumber'             => 'serial number',
            'organizationIdentifier'   => 'organization identifier',
            'organizationalUnitName'   => 'organization unit name',
            'organizationName'         => 'organization name',
            'country'                  => 'country code name',
            'address'                  => 'location',
            'businessCategory'         => 'industry',
        ];

        foreach ($requiredFields as $prop => $label) {
            if (empty($this->$prop)) {
                throw new CertificateBuilderException("$label is mandatory field");
            }
        }

        // SDK: invoiceType must be exactly 4 chars (already validated in setter, but verify)
        if (strlen($this->invoiceType) !== 4) {
            throw new CertificateBuilderException('Invalid invoice type, please provide a valid invoice type.');
        }

        // SDK VAT group validation: if org identifier 11th digit (index 10) == '1',
        // then OU must be a 10-digit TIN number.
        if (isset($this->organizationIdentifier[10])
            && $this->organizationIdentifier[10] === '1'
            && strlen($this->organizationalUnitName) !== 10
        ) {
            throw new CertificateBuilderException(
                'Organization Unit Name must be the 10-digit TIN number of the individual group member whose device is being onboarded.'
            );
        }
    }

    /**
     * Validate a field does not contain forbidden special characters.
     *
     * SDK (application.properties): specialCharacterRegex = [!@#$%&*_<]
     *
     * @param string $value     The field value to validate
     * @param string $fieldName The field name for error messages
     */
    private function validateFieldChars(string $value, string $fieldName): void {
        if (preg_match(self::FORBIDDEN_CHARS_REGEX, $value)) {
            throw new CertificateBuilderException(
                "Invalid $fieldName, The $fieldName should only contain alphanumeric characters, "
                . "and special characters ('!@#\$%&*_<=' including the symbol for 'ampersand' and 'less than') are not allowed."
            );
        }
    }

    /**
     * Create OpenSSL config array.
     *
     * @return array
     */
    private function createOpenSslConfig(): array {
        return [
            "digest_alg"       => "sha256",
            "private_key_bits" => 2048,
            "private_key_type" => OPENSSL_KEYTYPE_EC,
            "curve_name"       => "secp256k1",
            "req_extensions"   => "v3_req",
            "config"           => $this->createConfigFile()
        ];
    }

    /**
     * Resolve the OID extension value based on environment.
     */
    private function resolveOidValue(): string {
        return match ($this->environment) {
            self::ENV_PRODUCTION => self::OID_PROD,
            self::ENV_NONPROD    => self::OID_NONPROD,
            self::ENV_SIMULATION => self::OID_SIMULATION,
        };
    }

    /**
     * Create a temporary OpenSSL config file.
     *
     * The config embeds DN fields directly to control their ASN.1 order,
     * matching SDK output: C → OU → O → CN.
     *
     * @return string The path to the config file.
     * @throws CertificateBuilderException
     */
    private function createConfigFile(): string {
        $dirSection = [
            'SN'                => $this->serialNumber,
            'UID'               => $this->organizationIdentifier,
            'title'             => $this->invoiceType,
            'registeredAddress' => $this->address,
            'businessCategory'  => $this->businessCategory,
        ];

        $configContent = sprintf(
            self::CONFIG_TEMPLATE,
            $this->resolveOidValue()
        ) . "\n";

        foreach ($dirSection as $key => $value) {
            $configContent .= "$key = " . $this->escapeConfigValue($value) . "\n";
        }

        $tempFile = tempnam(sys_get_temp_dir(), 'zatca_');
        if ($tempFile === false) {
            throw new CertificateBuilderException('Failed to create temporary config file.');
        }

        try {
            (new Storage)->put($tempFile, $configContent);
        } catch (ZatcaStorageException $e) {
            throw new CertificateBuilderException('Failed to write temporary config file.', $e->getContext());
        }

        return $tempFile;
    }

    /**
     * Escape a value for use in OpenSSL config files.
     *
     * Values containing special characters (backslash, quotes, newlines) are
     * double-quoted and escaped to prevent config parsing issues.
     */
    private function escapeConfigValue(string $value): string {
        // If value contains characters that could break config parsing, wrap in quotes
        if (preg_match('/[\\\\"\n\r#;=]/', $value)) {
            return '"' . str_replace(['\\', '"'], ['\\\\', '\\"'], $value) . '"';
        }
        return $value;
    }

    /**
     * Generate keys and CSR.
     *
     * DN array order controls the ASN.1 field order in the CSR subject.
     * Ordered to match SDK (BouncyCastle X500NameBuilder): C → OU → O → CN.
     * The custom config file prevents OpenSSL from injecting default fields.
     *
     * @param array $config OpenSSL configuration array.
     */
    private function generateKeys(array $config): void {
        $this->privateKey = openssl_pkey_new($config);
        if ($this->privateKey === false) {
            throw new CertificateBuilderException('Key generation failed: ' . $this->getOpenSslErrors());
        }

        // SDK DN order: C → OU → O → CN
        $dn = [
            "C"                      => $this->country,
            "OU"                     => $this->organizationalUnitName,
            "O"                      => $this->organizationName,
            "CN"                     => $this->commonName,
        ];

        $this->csr = openssl_csr_new($dn, $this->privateKey, $config);
        if ($this->csr === false) {
            throw new CertificateBuilderException('CSR generation failed: ' . $this->getOpenSslErrors());
        }
    }

    /**
     * Retrieve all OpenSSL error messages.
     */
    private function getOpenSslErrors(): string {
        $errors = [];
        while ($msg = openssl_error_string()) {
            $errors[] = $msg;
        }
        return implode("; ", $errors);
    }

    /**
     * Free private key resource if necessary.
     */
    public function __destruct() {
        if ($this->privateKey && is_resource($this->privateKey)) {
            // In PHP 8.0+ this is a no-op; resource-based keys are legacy.
            openssl_pkey_free($this->privateKey);
        }
    }
}

