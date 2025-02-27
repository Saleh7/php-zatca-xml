<?php
require __DIR__ . '/../vendor/autoload.php';

use Saleh7\Zatca\Helpers\Certificate;
use Saleh7\Zatca\InvoiceSigner;

// get invoice.xml ..
$xmlInvoice = file_get_contents(__DIR__ .'/output/GeneratorStandard_Invoice.xml');

// get from ZATCA certificate ..
$json_certificate = file_get_contents(__DIR__ .'/Certificates/output/ZATCA_certificate_data.json');

// Decode JSON
$json_data = json_decode($json_certificate, true, 512, JSON_THROW_ON_ERROR);

// get certificate
$certificate = $json_data['certificate'];

//get secret 
$secret = $json_data['secret'];

// get private key
$privateKey = file_get_contents(__DIR__ .'/Certificates/output/private.pem');

$cleanPrivateKey = trim(str_replace(["-----BEGIN PRIVATE KEY-----", "-----END PRIVATE KEY-----"], "", $privateKey));

$certificate = (new Certificate(
    $certificate,
    $cleanPrivateKey,
    $secret 
)); 
// $signedInvoice = InvoiceSigner::signInvoice($xmlInvoice, $certificate);
// echo $signedInvoice->getInvoice();
// echo $signedInvoice->getHash();

// save output/signed_invoice.xml
InvoiceSigner::signInvoice($xmlInvoice, $certificate)->saveXMLFile('GeneratorStandard_Invoice_Signed.xml');