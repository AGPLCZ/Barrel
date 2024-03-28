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
if ($userDatabase['btc_address']) {
    echo "Vaše adresa: " . ($userDatabase['btc_address']) . "<br>";
}
if ($userDatabase['total_btc']) {
    echo "Nakoupeno: " . ($userDatabase['total_btc']) . " BTC<br>";
}
if ($userDatabase['total_fee']) {
    echo "Poplatek burzy: " . ($userDatabase['total_fee']) . " Kč<br>";
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
        if ($price) {
            echo "Nakoupeno: " . $price['total'] . "BTC";
        }
    }
}

/*
 // STATUS
 if (isset($userDatabase) && isset($userDatabase['order_id'])) {

    $orderId = $userDatabase['order_id'];
    $price = getTransactionDetails($clientId, $publicKey, $privateKey, $nonce, $orderId);
    if ($price) {
        echo "Nakoupeno: " . $price['total'] . "BTC";
        echo "<br>";
        //echo $price['fee'];
    }
}
*/

// ODESLAT
if (isset($_POST["submit"])) {
    $validator = new Kielabokkie\Bitcoin\AddressValidator();
    $address = $_POST["address"];

    $userDatabase = getUserData($userString);

    if ($userDatabase['total_fee']) {
        if ($validator->isValid($address)) {
            $info = sendBTC($userString, $clientId, $publicKey, $privateKey, $nonce, $address, $userDatabase['total_fee'], $orderId);

            if ($info){
                echo $info['withdrawalId'];
                echo $info['status'];
                echo $info['error'];
            }

        } else {
            echo
            "Bitcoinová adresa je neplatná.";
        }
    }
}

/*
Chyba, směna na Coinmate nebyla realizována. Vaše adresa: bc1q9aslcgj4203926f45aazn2cnzmvwhc0g5d5h5j
Warning: Undefined variable $price in /home/html/dobrodruzi.cz/public_html/www/barrel/index.php on line 76
array(3) { ["error"]=> bool(true) ["errorMessage"]=> string(18) "Api internal error" ["data"]=> NULL }
Warning: Undefined variable $status in /home/html/dobrodruzi.cz/public_html/www/barrel/functions_api.php on line 273

Warning: Undefined variable $error in /home/html/dobrodruzi.cz/public_html/www/barrel/functions_api.php on line 274
*/
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