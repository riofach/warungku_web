<?php

require __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;
use GuzzleHttp\Client;

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Configuration from .env
// We need to fetch from $_ENV or getenv() depending on how Dotenv loads
$merchantCode = $_ENV['DUITKU_MERCHANT_CODE'] ?? getenv('DUITKU_MERCHANT_CODE') ?? 'DS28004';
$apiKey = $_ENV['DUITKU_API_KEY'] ?? getenv('DUITKU_API_KEY') ?? '6a806939df226085e92158592f592dac';
$baseUrl = $_ENV['APP_URL'] ?? getenv('APP_URL') ?? 'http://localhost:8000'; // Target URL (Ngrok or Localhost)

// Get Order ID from command line argument
$orderId = $argv[1] ?? null;
// Get Amount from command line argument (optional, default 94000)
$amount = $argv[2] ?? 94000; 

if (!$orderId) {
    echo "Usage: php test-webhook.php [ORDER_CODE] [AMOUNT]\n";
    echo "Example: php test-webhook.php WRG-20260209-0001 94000\n";
    exit(1);
}

// Payment Service verifySignature check:
// MD5(merchantCode + amount + merchantOrderId + apiKey)
$resultCode = '00'; // Success
$merchantOrderId = $orderId;
$signatureParams = $merchantCode . $amount . $merchantOrderId . $apiKey;
$signature = md5($signatureParams);

echo "--------------------------------------------------\n";
echo "Simulating Duitku Webhook for Order: $merchantOrderId\n";
echo "Target URL: $baseUrl/webhook/payment\n";
echo "Merchant Code: $merchantCode\n";
echo "Amount: $amount\n";
echo "Signature: $signature\n";
echo "--------------------------------------------------\n";

$client = new Client();

try {
    $response = $client->post("$baseUrl/webhook/payment", [
        'form_params' => [
            'merchantCode' => $merchantCode,
            'amount' => $amount,
            'merchantOrderId' => $merchantOrderId,
            'resultCode' => $resultCode, // '00' Success
            'reference' => 'TEST-REF-' . time(),
            'signature' => $signature,
            'paymentCode' => 'VC', // Dummy Payment Code
        ]
    ]);

    echo "Status Code: " . $response->getStatusCode() . "\n";
    echo "Response Body: " . $response->getBody() . "\n";
    
    if ($response->getStatusCode() == 200) {
        echo "✅ SUCCESS! Order status should be 'paid' now.\n";
    } else {
        echo "❌ FAILED! Check Laravel logs.\n";
    }

} catch (\GuzzleHttp\Exception\ClientException $e) {
    echo "❌ CLIENT ERROR (4xx): " . $e->getMessage() . "\n";
    if ($e->hasResponse()) {
        echo "Response: " . $e->getResponse()->getBody() . "\n";
    }
} catch (\GuzzleHttp\Exception\ServerException $e) {
    echo "❌ SERVER ERROR (5xx): " . $e->getMessage() . "\n";
    if ($e->hasResponse()) {
        echo "Response: " . $e->getResponse()->getBody() . "\n";
    }
} catch (\Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}
