<?php
// Definování proměnných
$amountFrom = 10;
$btcPriority = "high";
$currencyFrom = "USD";
$currencyTo = "BTC";
$address = "3J98t…NLy";
$paymentMethodId = "BITCOIN_BTC";
$reference = "ReferenceString";

// Příprava dat ve formátu JSON s vloženými proměnnými
$postFields = json_encode(array(
    "amountFrom" => $amountFrom,
    "btcPriority" => $btcPriority,
    "currencyFrom" => $currencyFrom,
    "currencyTo" => $currencyTo,
    "address" => $address,
    "paymentMethodId" => $paymentMethodId,
    "reference" => $reference
));

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://confirmo.net/api/v3/payouts");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
curl_setopt($ch, CURLOPT_HEADER, FALSE);

curl_setopt($ch, CURLOPT_POST, TRUE);

// Vložení upravených dat do CURLOPT_POSTFIELDS
curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);

// Nezapomeňte přidat vaše vlastní hlavičky, včetně hlavičky Authorization
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
  "Content-Type: application/json",
  // "Authorization: Bearer {VÁŠ_API_KLÍČ}" - nahraďte {VÁŠ_API_KLÍČ} vaším skutečným API klíčem
));

$response = curl_exec($ch);
curl_close($ch);

var_dump($response);
