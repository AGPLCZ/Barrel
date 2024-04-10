<?php
$id = "";
$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, "https://confirmo.net/api/v3/invoices/$id");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
curl_setopt($ch, CURLOPT_HEADER, FALSE);

// Vložení hlaviček, včetně autentizace
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    "Content-Type: application/json",
    "Authorization: Bearer {VÁŠ_API_KLÍČ}"  // Nahraďte {VÁŠ_API_KLÍČ} vaším skutečným API klíčem
  ));

$response = curl_exec($ch);
curl_close($ch);

var_dump($response);