<?php
ini_set("display_errors", 1);
error_reporting(E_ERROR | E_WARNING);

require_once "config.php";
require_once 'functions_api.php';
require_once 'functions_atm.php';
require_once 'vendor/autoload.php';



// ADRESA
$url = url();
$userString = $url['urlss'];
echo "Id číslo objednávky: " . $userString . "<br>";

// NAŠTENÍ SQL DAT PRO DALŠÍ POUŽITÍ
$userDatabase = getUserData($userString);


// VLOŽENO BANKOVEK
$totalAmount = user($userString);
echo "Vloženo: " . $totalAmount . " Kč<br>";


//ZOBRAZENÍ
//echo ($userDatabase['order_id']);
if (isset($userDatabase['btc_address'])) {
    echo "Vaše adresa:" . ($userDatabase['btc_address']);
}


//NÁKUP
if (isset($userDatabase) && is_null($userDatabase['order_id'])) {
    // Provést nákup, pokud existují data uživatele a 'order_id' je NULL
    $orderId = executePurchase($userDatabase, $clientId, $publicKey, $privateKey, $nonce);
    if ($orderId) {
        redirection($base_url, $userString);
        exit;
    }
}


// ULOŽIT SMĚNY Z COINMATE DO "transactions" DATABÁZE
if (isset($userDatabase) && isset($userDatabase['order_id'])) {
    $orderId = $userDatabase['order_id'];

    // Řádek nebyl nalezen, uložit do databáze
    $transactionsDatabase = getTransactionsOrderId($orderId);
    if (empty($transactionsDatabase)) {
        $price = saveTransactionDetails($clientId, $publicKey, $privateKey, $nonce, $orderId);
        echo "Nakoupeno: " . $price['total'] . "BTC";
    }
}




// ODESLAT
if (isset($_POST["submit"])) {
    $validator = new Kielabokkie\Bitcoin\AddressValidator();
    $address = $_POST["address"];


    // STATUS
    if (isset($userDatabase) && isset($userDatabase['order_id'])) {
        $orderId = $userDatabase['order_id'];
        $price = getTransactionDetails($clientId, $publicKey, $privateKey, $nonce, $orderId);
        echo "Nakoupeno: " . $price['total'] . "BTC";
        echo "<br>";
        //echo $price['fee'];
    }

    if ($validator->isValid($address)) {
        echo "Vaše adresa: " . $address;
        sendBTC($userString, $clientId, $publicKey, $privateKey, $nonce, $address, $price, $orderId);
    } else {
        echo
        "Bitcoinová adresa je neplatná.";
    }
}



?>


<!doctype html>
<html lang="cs">

<head>
</head>

<body>

    <?php

    if (!isset($_POST["submit"]) && ($userDatabase['btc_address'] == NULL)) :

    ?>
        <form action="index.php" method="post">
            <input type="text" name="address" placeholder="BTC adresa">
            <button type="submit" name="submit">Odeslat</button>
        </form>

    <? endif ?>
</body>

</html>