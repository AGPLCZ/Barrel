<?php

// Generování podpisu
/*
$signature = createSignature($clientId, $publicKey, $privateKey, $nonce);
echo $signature;
*/
function createSignature($clientId, $publicKey, $privateKey, $nonce)
{
    // Spojení nonce, client ID a public API key do jednoho řetězce
    $message = $nonce . $clientId . $publicKey;

    // Vytvoření podpisu pomocí HMAC-SHA256
    $signature = hash_hmac('sha256', $message, $privateKey);

    // Převod podpisu na velká písmena (není povinné, záleží na specifikaci API)
    return strtoupper($signature);
}

// BUY
function buy($clientId, $publicKey, $privateKey, $nonce, $totalAmount)
{

    $amountCZK = $totalAmount; // Částka pro nákup BTC, získaná z předchozího skriptu
    $currencyPair = 'BTC_CZK'; // Měnový pár pro nákup


    // Generování podpisu
    $signature = createSignature($clientId, $publicKey, $privateKey, $nonce);
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, "https://coinmate.io/api/buyInstant");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_HEADER, FALSE);
    curl_setopt($ch, CURLOPT_POST, TRUE);

    // Přidání potřebných parametrů včetně podpisu
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'total' => $amountCZK,
        'currencyPair' => $currencyPair,
        'clientId' => $clientId,
        'publicKey' => $publicKey,
        'nonce' => $nonce,
        'signature' => $signature
    ]));

    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/x-www-form-urlencoded"
    ]);

    $response = curl_exec($ch);
    curl_close($ch);

    // Dekódování a zobrazení odpovědi
    $responseData = json_decode($response, true);
    var_dump($responseData);


    // Získané údaje z API odpovědi
    if (!$responseData['error'] && isset($responseData['data'])) {
        $orderId = $responseData['data']; // ID transakce z Coinmate
        echo $orderId;
        return $orderId;
    } else {
        echo "Chyba, směna na Coinmate nebyla realizována. ";
    }
}



function saveTransactionDetails_org($clientId, $publicKey, $privateKey, $nonce, $orderId)
{

    // Generování podpisu
    $signature = createSignature($clientId, $publicKey, $privateKey, $nonce);

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, "https://coinmate.io/api/transactionHistory");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_HEADER, FALSE);
    curl_setopt($ch, CURLOPT_POST, TRUE);

    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'clientId' => $clientId,
        'publicKey' => $publicKey,
        'nonce' => $nonce,
        'signature' => $signature,
        'limit' => 50, // Nastavte limit podle vašich potřeb
        'sort' => 'DESC', // Nejnovější transakce první
        'orderId' => $orderId // Filtruje transakce podle ID pokynu
    ]));

    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/x-www-form-urlencoded"
    ]);

    $response = curl_exec($ch);
    curl_close($ch);

    $responseData = json_decode($response, true);

    $totalBTC = 0;
    $totalFee = 0;

    if (!$responseData['error'] && isset($responseData['data'])) {
        foreach ($responseData['data'] as $transaction) {

            if ($transaction['orderId'] == $orderId) {
                $totalBTC += $transaction['amount'];
                $totalFee += $transaction['fee'];
                $transactionId = $transaction['transactionId'];
            }
        }
    }

    //$totalBTC = number_format($totalBTC, 8);
    //$totalFee = number_format($totalFee, 8);


    var_dump($responseData);

    $userDatabase = DB::queryFirstRow("SELECT * FROM users WHERE order_id=%s", $orderId);
    if (isset($userDatabase) && isset($userDatabase['user_id'])) {
        $userId = $userDatabase['user_id'];
    } else {
        echo "Chyba, neexistuje userId / user_id";
        exit;
    }

    // Vložení údajů o transakci do tabulky `transactions`
    DB::insert('transactions', array(
        'user_id' => $userId,
        'amount_btc' => $totalBTC,
        'transaction_fee' => $transaction['fee'],
        'total_fee' => $totalFee,
        'order_id' => $orderId,
        'transaction_id' => $transactionId
        // 'created_at' a 'updated_at' by měly být automaticky nastaveny, pokud jste je definovali jako TIMESTAMP v SQL
    ));

    // Kontrola úspěchu
    if (DB::affectedRows() > 0) {
        return [
            'total' => $totalBTC,
            'fee' => $totalFee
        ];
    } else {
        return "Nepodařilo se uložit transakci do databáze.";
    }
}




function saveTransactionDetails_gpt($clientId, $publicKey, $privateKey, $nonce, $orderId)
{
    // Generování podpisu
    $signature = createSignature($clientId, $publicKey, $privateKey, $nonce);

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, "https://coinmate.io/api/transactionHistory");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_HEADER, FALSE);
    curl_setopt($ch, CURLOPT_POST, TRUE);

    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'clientId' => $clientId,
        'publicKey' => $publicKey,
        'nonce' => $nonce,
        'signature' => $signature,
        'limit' => 50, // Nastavte limit podle vašich potřeb
        'sort' => 'DESC', // Nejnovější transakce první
        'orderId' => $orderId // Filtruje transakce podle ID pokynu
    ]));

    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/x-www-form-urlencoded"
    ]);

    $response = curl_exec($ch);
    curl_close($ch);

    $responseData = json_decode($response, true);

    $userDatabase = DB::queryFirstRow("SELECT * FROM users WHERE order_id=%s", $orderId);
    if (!isset($userDatabase) || !isset($userDatabase['user_id'])) {
        echo "Chyba, neexistuje userId / user_id";
        exit;
    }
    $userId = $userDatabase['user_id'];

    if (!$responseData['error'] && isset($responseData['data'])) {
        foreach ($responseData['data'] as $transaction) {
            if ($transaction['orderId'] == $orderId) {
                // Vložení každé jednotlivé transakce do tabulky `transactions`
                DB::insert('transactions', array(
                    'user_id' => $userId,
                    'amount_btc' => $transaction['amount'],
                    'transaction_fee' => $transaction['fee'],
                    'order_id' => $orderId,
                    'transaction_id' => $transaction['transactionId']
                    // 'created_at' a 'updated_at' by měly být automaticky nastaveny, pokud jste je definovali jako TIMESTAMP v SQL
                ));
            }
        }
    }

    $totalBTC = 0;
    $totalFee = 0;

    if (!$responseData['error'] && isset($responseData['data'])) {
        foreach ($responseData['data'] as $transaction) {

            if ($transaction['orderId'] == $orderId) {
                $totalBTC += $transaction['amount'];
                $totalFee += $transaction['fee'];
            }
        }
    }

    // Aktualizace součtu poplatků v tabulce `users`
    DB::query("UPDATE users SET total_fee = total_fee + %d WHERE user_id=%s", $totalFee, $userId);

    // Kontrola úspěchu
    if (DB::affectedRows() > 0) {
        return [
            'total' => $totalBTC,
            'fee' => $totalFee
        ];
    } else {
        return "Nepodařilo se uložit transakci do databáze.";
    }
}




function saveTransactionDetails($clientId, $publicKey, $privateKey, $nonce, $orderId)
{

    $userDatabase = DB::queryFirstRow("SELECT * FROM users WHERE order_id=%s", $orderId);
    if (isset($userDatabase) && !isset($userDatabase['total_btc'])) {



        // Generování podpisu
        $signature = createSignature($clientId, $publicKey, $privateKey, $nonce);

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://coinmate.io/api/transactionHistory");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_POST, TRUE);

        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'clientId' => $clientId,
            'publicKey' => $publicKey,
            'nonce' => $nonce,
            'signature' => $signature,
            'limit' => 50, // Nastavte limit podle vašich potřeb
            'sort' => 'DESC', // Nejnovější transakce první
            'orderId' => $orderId // Filtruje transakce podle ID pokynu
        ]));

        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/x-www-form-urlencoded"
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        $responseData = json_decode($response, true);


        $totalBTC = 0;
        $totalFee = 0;

        if (!$responseData['error'] && isset($responseData['data'])) {
            foreach ($responseData['data'] as $transaction) {

                if ($transaction['orderId'] == $orderId) {
                    $totalBTC += $transaction['amount'];
                    $totalFee += $transaction['fee'];
                }
            }
        }

        DB::update('users', [
            'total_fee' => $totalFee,
            'total_btc' => $totalBTC
        ], "order_id=%s", $orderId);


        return [
            'total' => $totalBTC,
            'fee' => $totalFee
        ];
    }
}





function getTransactionDetails($clientId, $publicKey, $privateKey, $nonce, $orderId)
{

    // Generování podpisu
    $signature = createSignature($clientId, $publicKey, $privateKey, $nonce);

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, "https://coinmate.io/api/transactionHistory");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_HEADER, FALSE);
    curl_setopt($ch, CURLOPT_POST, TRUE);

    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'clientId' => $clientId,
        'publicKey' => $publicKey,
        'nonce' => $nonce,
        'signature' => $signature,
        'limit' => 50, // Nastavte limit podle vašich potřeb
        'sort' => 'DESC', // Nejnovější transakce první
        'orderId' => $orderId // Filtruje transakce podle ID pokynu
    ]));

    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/x-www-form-urlencoded"
    ]);

    $response = curl_exec($ch);
    curl_close($ch);

    $responseData = json_decode($response, true);

    $totalBTC = 0;
    $totalFee = 0;

    if (!$responseData['error'] && isset($responseData['data'])) {
        foreach ($responseData['data'] as $transaction) {

            if ($transaction['orderId'] == $orderId) {
                $totalBTC += $transaction['amount'];
                $totalFee += $transaction['fee'];
            }
        }
    }
    //var_dump($responseData);
    $totalBTC = number_format($totalBTC, 8);
    $totalFee = number_format($totalFee, 8);

    //echo "Nakoupeno: " . $totalBTC . " BTC<br>";
    //echo "Poplatek: " . $totalFee . " CZK<br>";

    return [
        'total' => $totalBTC,
        'fee' => $totalFee
    ];
}


function withdrawal($clientId, $publicKey, $privateKey, $nonce, $address, $amount, $orderId)
{

    //$address ="bc1q9aslcgj4203926f45aazn2cnzmvwhc0g5d5h5j";
    //$amount = "00009";
    $signature = createSignature($clientId, $publicKey, $privateKey, $nonce);

    $ch = curl_init();

    // Nastavení URL endpointu pro vaši operaci
    curl_setopt($ch, CURLOPT_URL, "https://coinmate.io/api/bitcoinWithdrawal");

    // Nastavení, že se bude jednat o POST požadavek
    curl_setopt($ch, CURLOPT_POST, TRUE);

    // Přidání potřebných parametrů včetně podpisu
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array(
        'clientId' => $clientId,
        'publicKey' => $publicKey,
        'nonce' => $nonce,
        'signature' => $signature,
        // Přidejte další specifické parametry pro vaši operaci zde
        'amount' => $amount, // Příklad: částka k odeslání
        'address' => $address, // Příklad: cílová Bitcoin adresa
        'feePriority' => 'LOW' // Příklad: priorita poplatku
    )));

    // Nastavení, že očekáváme návratovou hodnotu jako řetězec
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

    $response = curl_exec($ch);
    curl_close($ch);

    // Dekódování a zobrazení odpovědi
    $responseData = json_decode($response, true);
    //var_dump($responseData);


    // Inicializace proměnné
    $withdrawalId = null;
    $error = null;



    // Kontrola, zda neexistuje chyba a jestli je 'data' k dispozici
    if (!$responseData['error'] && is_null($responseData['errorMessage']) && isset($responseData['data'])) {
        $withdrawalId = $responseData['data'];
        $error = $responseData['errorMessage'];

        DB::update('users', [
            'withdrawal_id' => $withdrawalId,
            'error' => $error
        ], "user_string=%s", $orderId);
    }


    return $withdrawalId;
}





//Ověření 

/*
if ($status == "OK") {
    $user_status = "Status: " . "Transakce byla přijata na vaši peněženku" . "<br>";
} elseif ($status == "NEW") {
    $user_status = "Status: " . "Transakce byla zaregistrována, ale ještě nebyla zpracována" . "<br>";
} elseif ($status == "SENT") {
    $user_status = "Status: " . "Transakce byla odeslána" . "<br>";
} elseif ($status == "CANCELED") {
    $user_status = "Status: " . "Transakce byla zrušena, zkuste to později nebo kontaktujte technickou podporu." . $status . "<br>";
} else {
    $user_status = $status;
}
*/