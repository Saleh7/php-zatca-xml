<?php
require __DIR__ . '/../../vendor/autoload.php';

use Saleh7\Zatca\ZatcaAPI;
use Saleh7\Zatca\Exceptions\ZatcaApiException;

// Environment: 'sandbox', 'simulation', or 'production'
$zatcaClient = new ZatcaAPI('sandbox');

try {
    // Sandbox OTP: 123345 | Production: get from fatoora.zatca.gov.sa
    $otp = "123345";
    $certificatePath = __DIR__ . '/output/certificate.csr';
    $csr = $zatcaClient->loadCSRFromFile($certificatePath);
    
    $complianceResult = $zatcaClient->requestComplianceCertificate($csr, $otp);
    
    echo "Compliance Certificate:\n" . $complianceResult->getCertificate() . "\n";
    echo "API Secret: " . $complianceResult->getSecret() . "\n";
    echo "Request ID: " . $complianceResult->getRequestId() . "\n";

    $outputFile = __DIR__ . '/output/ZATCA_certificate_data.json';
    $zatcaClient->saveToJson(
        $complianceResult->getCertificate(),
        $complianceResult->getSecret(),
        $complianceResult->getRequestId(),
        $outputFile
    );
    
    echo "Certificate data saved to {$outputFile}\n";
    
} catch (ZatcaApiException $e) {
    echo "API Error: " . $e->getMessage();
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
