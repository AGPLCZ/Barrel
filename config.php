<?php

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try {
    $conn = new mysqli("127.0.0.1", "dobrodruzi.cz", "e4gXbzJ7qmtM", "coinmate");
    $conn->set_charset("utf8mb4");
} catch (Exception $e) {
    error_log($e->getMessage());
    exit('Error connecting to database'); //Should be a message a typical user could understand
}


require_once 'db.class.php'; // Nebo cestu k vaší instalaci MeekroDB

// Nastavení připojení k databázi
DB::$user = 'dobrodruzi.cz';
DB::$password = 'xxxxxxxx';
DB::$dbName = 'coinmate';
DB::$host = '127.0.0.1'; // Obvykle 'localhost'
DB::$encoding = 'utf8mb4'; // Nastavení kódování



// Příklad použití
$clientId = 'xxxxx'; // Vaše ID klienta
$publicKey = 'y-uRuoxxxxxxxxxxxxxxxxxxxxxxxxxxxrdnti4'; // Váš veřejný klíč
$privateKey = 'c7BGkbyxxxxxxxxxxxxxxxxxxxxxxxxxxrV3f4'; // Váš soukromý klíč
$nonce = time(); // Použití unixového časového razítka jako nonce

