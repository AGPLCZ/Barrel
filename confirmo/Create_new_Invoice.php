<?php
//https://confirmonetapi.docs.apiary.io/#introduction/invoice-currency-combinations
// Definování proměnných pro data faktury
$description = "small silver computer + 4 years guarantee";
$name = "Computer";
$amount = "1000.00";
$currencyFrom = "EUR";
$currency = "EUR"; // Měna pro vyrovnání
$notifyEmail = "orderReceived@yourEShop.com";
$notifyUrl = "https://yourEShop.com/orderReceived";
$returnUrl = "https://yourEShop.com/orderReceived";
$reference = "anything";

// Příprava dat ve formátu JSON
$postFields = json_encode(array(
    "product" => array(
        "description" => $description,
        "name" => $name
    ),
    "invoice" => array(
        "amount" => $amount,
        "currencyFrom" => $currencyFrom
    ),
    "settlement" => array(
        "currency" => $currency
    ),
    "notifyEmail" => $notifyEmail,
    "notifyUrl" => $notifyUrl,
    "returnUrl" => $returnUrl,
    "reference" => $reference
));

$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, "https://confirmo.net/api/v3/invoices");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
curl_setopt($ch, CURLOPT_HEADER, FALSE);

curl_setopt($ch, CURLOPT_POST, TRUE);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);

// Vložení hlaviček, včetně autentizace
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
  "Content-Type: application/json",
  "Authorization: Bearer {VÁŠ_API_KLÍČ}"  // Nahraďte {VÁŠ_API_KLÍČ} vaším skutečným API klíčem
));

$response = curl_exec($ch);
curl_close($ch);

var_dump($response);
