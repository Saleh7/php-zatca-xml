<?php
require __DIR__ . '/../vendor/autoload.php';

use Saleh7\Zatca\Helpers\Certificate;
use Saleh7\Zatca\InvoiceSigner;

// get invoice.xml ..
$xmlInvoice = file_get_contents(__DIR__ .'/output/unsigned_invoice.xml');

// get from ZATCA certificate ..
$json_certificate = file_get_contents(__DIR__ .'/output/ZATCA_certificate_data.json');

// Decode JSON
$json_data = json_decode($json_certificate, true, 512, JSON_THROW_ON_ERROR);

// get certificate
$certificate = $json_data[0]['certificate'];

//get secret 
$secret = $json_data[0]['secret'];

// get private key
$privateKey = file_get_contents(__DIR__ .'/output/private.pem');

$claenPrivateKey = trim(str_replace(["-----BEGIN PRIVATE KEY-----", "-----END PRIVATE KEY-----"], "", $privateKey));

$certificate = (new Certificate(
    $certificate,
    $claenPrivateKey,
    $secret 
)); 

// $signedInvoice = InvoiceSigner::signInvoice($xmlInvoice, $certificate);
// echo $signedInvoice->getInvoice();
// echo $signedInvoice->getHash();

// save output/signed_invoice.xml
InvoiceSigner::signInvoice($xmlInvoice, $certificate)->saveXMLFile();