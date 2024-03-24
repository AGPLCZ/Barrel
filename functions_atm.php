<?php
$base_url = "https://" . $_SERVER['SERVER_NAME'];

function url()
{
    // Odstranění počátečního a koncového lomítka z URI
    $urlPath = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');

    // Rozdělení cesty na segmenty
    $urlSegments = explode('/', $urlPath);

    // První segment cesty (např. 'articles')
    $urls = isset($urlSegments[0]) ? $urlSegments[0] : '';

    // Druhý segment cesty (např. 'nazev_clanku'), pokud existuje
    $urlss = isset($urlSegments[1]) ? $urlSegments[1] : '';


    // Vrácení obou hodnot v poli

    return [
        'urls' => $urls,
        'urlss' => $urlss
    ];
}

function redirection($base_url, $userString)
{
    echo $base_url . "/" .  "barrel/" . $userString  . "/";
    header("Location: " . $base_url . "/" .  "barrel/" . $userString  . "/");
    exit();
}

function url_user()
{
    $url = url();
    $userString = $url['urlss'];
    //echo "Vaše tajná adresa pro výběr: https:dobrodruzi.cz/coinmate/" . $userString . "<br>";
    return  "https:dobrodruzi.cz/coinmate/" . $userString;
}

function user($userString)
{
    // Získáme všechny záznamy s odpovídajícím user_string
    $users = DB::query("SELECT * FROM users WHERE user_string=%s", $userString);
    $totalAmount = 0;

    // Procházíme všechny získané záznamy a sčítáme částky
    foreach ($users as $user) {
        $totalAmount += $user['total_amount_czk'];
    }
    //echo "Celková vložená částka: $totalAmount CZK<br>";

    if (!$users) {
        echo "Uživatel nebyl nalezen. Kontaktujte technickou podporu.";
        return 0;
    } else {
        return $totalAmount;
    }
}


function getUserData($userString)
{
    $query = DB::queryFirstRow("SELECT * FROM users WHERE user_string=%s", $userString);
    if (!$query) {
        echo "Uživatel nebyl nalezen. Kontaktujte technickou podporu.";
        die; // Zastaví vykonávání skriptu
    } else {
        return $query;
    }
}

function getTransactionsOrderId($order_id)
{
    $query = DB::queryFirstRow("SELECT * FROM transactions WHERE order_id=%s", $order_id);
    if (!$query) {
        echo "error_id:12";
        die; // Zastaví vykonávání skriptu
    } else {
        return $query;
    }
}


function executePurchase($user, $clientId, $publicKey, $privateKey, $nonce)
{
    if (is_null($user['buy'])) {
        $orderId = buy($clientId, $publicKey, $privateKey, $nonce, $user['total_amount_czk']);
        if ($orderId) {
            DB::update('users', [
                'buy' => 'NAKOUPENO',
                'order_id' => $orderId
            ], "user_string=%s", $user['user_string']);
            return $orderId;
        }
    }
    return null;
}


function sendBTC($userString, $clientId, $publicKey, $privateKey, $nonce, $address, $amount, $orderId)
{
   

    if ($userString) {
        DB::update('users', [
            'btc_address' => $address,
        ], "user_string=%s", $userString);
    }

    return withdrawal($clientId, $publicKey, $privateKey, $nonce, $address, $amount, $orderId);

}



function displayUserStatus($user)
{
    if ($user['buy'] == 'NAKOUPENO') {
        echo "Nákup na Coinmate úspěšně proveden.<br>";
        // Zde můžete přidat logiku pro zobrazení dalších informací nebo formulářů
    } else {
        echo "Uživatel s identifikátorem {$user['user_string']} nebyl nalezen nebo jiný problém.";
    }
}




function isValidBTCAddress($address)
{
    if (preg_match('/^1[a-km-zA-HJ-NP-Z1-9]{25,34}$/', $address)) {
        return true; // P2PKH
    } elseif (preg_match('/^3[a-km-zA-HJ-NP-Z1-9]{25,34}$/', $address)) {
        return true; // P2SH
    } elseif (preg_match('/^bc1[a-z0-9]{39,59}$/', $address)) {
        return true; // Bech32
    }
    return false;
}
