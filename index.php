<?php
ini_set("display_errors", 1);
error_reporting(E_ERROR | E_WARNING);

require_once "config.php";
require_once 'functions_api.php';
require_once 'functions_atm.php';

// ADRESA
$url = url();
$userString = $url['urlss'];

// VLOŽENO BANKOVEK
$totalAmount = user($userString);
echo "Vloženo:" . $totalAmount;
$userDatabase = getUserData($userString);



//NÁKUP
if (isset($userDatabase) && is_null($userDatabase['order_id'])) {
    // Provést nákup, pokud existují data uživatele a 'order_id' je NULL
    $orderId = executePurchase($userDatabase, $clientId, $publicKey, $privateKey, $nonce);
    if ($orderId) {
        redirection($base_url, $userString);
    }
}


// ULOŽIT SMĚNY Z COINMATE DO "transactions" DATABÁZE
if (isset($userDatabase) && isset($userDatabase['order_id'])) {
    $orderId = $userDatabase['order_id'];

    // Řádek nebyl nalezen, uložit do databáze
    $transactionsDatabase = getTransactionsOrderId($orderId);
    if (empty($transactionsDatabase)) {
        saveTransactionDetails($clientId, $publicKey, $privateKey, $nonce, $orderId);
    }
}








/*
displayUserStatus($user);
sendBTC($user, $clientId, $publicKey, $privateKey, $nonce);
*/