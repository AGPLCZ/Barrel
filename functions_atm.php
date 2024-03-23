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

    return $totalAmount;
}


function getUserData($userString)
{
    return DB::queryFirstRow("SELECT * FROM users WHERE user_string=%s", $userString);
}

function getTransactionsOrderId($order_id)
{
    return DB::queryFirstRow("SELECT * FROM transactions WHERE order_id=%s", $order_id);
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


function sendBTC($userString, $clientId, $publicKey, $privateKey, $nonce, $adress)
{
    $totalBTC = getTransactionDetails($clientId, $publicKey, $privateKey, $nonce, $userString['order_id']);
    withdrawal($clientId, $publicKey, $privateKey, $nonce, $adress, $totalBTC['total']);


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
