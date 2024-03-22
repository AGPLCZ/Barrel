<?php
ini_set("display_errors", 1);
error_reporting(E_ERROR | E_WARNING);

require_once "config.php";
require_once 'functions_api.php';
require_once 'functions_atm.php';

$totalAmount = user();


$url = url();
$userString = $url['urlss'];


$user = getUserData($userString);

if ($user) {
    $orderId = executePurchase($user, $clientId, $publicKey, $privateKey, $nonce);
    if ($orderId) {
        // Logika pro pokračování po úspěšném nákupu
    }
    displayUserStatus($user);
    sendBTC($user, $clientId, $publicKey, $privateKey, $nonce);
}
