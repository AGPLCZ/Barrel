<?php 
$data = array("key" => "value"); // Data, která chcete odeslat
$jsonData = json_encode($data); // Kódování dat do JSON formátu

// Inicializace cURL sezení
$ch = curl_init('http://example.com/api/endpoint'); // URL cílového endpointu

// Nastavení cURL možností
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST"); // Nastavení metody na POST
curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData); // Přiřazení JSON dat jako tělo požadavku
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Vrácení výsledku místo výpisu
curl_setopt($ch, CURLOPT_HTTPHEADER, array(            
    'Content-Type: application/json', // Nastavení hlavičky na JSON
    'Content-Length: ' . strlen($jsonData)) // Délka obsahu
);

$result = curl_exec($ch); // Spuštění cURL sezení a uchování výsledku
curl_close($ch); // Uzavření cURL sezení


$jsonData = file_get_contents('php://input'); // Přijetí RAW JSON dat
$data = json_decode($jsonData, true); // Dekódování JSON dat do pole (true znamená asociační pole)

//--------------------------------


//Příklad bez CURLOPT_RETURNTRANSFER (výpis):
$ch = curl_init('http://example.com');
curl_exec($ch); // Odpověď se vypíše přímo
curl_close($ch);


// Příklad s CURLOPT_RETURNTRANSFER (výsledek uložený v proměnné):
$ch = curl_init('http://example.com');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch); // Odpověď je uložena v proměnné $response
curl_close($ch);

// Nyní můžete s $response dále pracovat
echo $response; // Výpis odpovědi, když se rozhodnete


//Použitím curl_setopt() s CURLOPT_URL: Můžete inicializovat cURL sezení bez URL a nastavit nebo změnit URL později pomocí curl_setopt().
$ch = curl_init(); // Inicializace bez URL
curl_setopt($ch, CURLOPT_URL, "https://coinmate.io/api/buyInstant"); // Nastavení URL
// Další nastavení cURL a operace...


//-------------------


$ch = curl_init('http://example.com/api/endpoint'); // Inicializace cURL sezení s URL

curl_setopt($ch, CURLOPT_POST, TRUE); // Nastavení, že se bude jednat o POST požadavek

// Nastavení dat, která chcete odeslat s POST požadavkem
$data = array('key1' => 'value1', 'key2' => 'value2');
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

$result = curl_exec($ch); // Spuštění cURL sezení a uložení výsledku
curl_close($ch); // Uzavření cURL sezení


//--------------------


// Příprava dat, která chcete odeslat
$data = array(
    'key1' => 'value1',
    'key2' => 'value2',
    'key3' => 'value3'
);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://example.com/submit");
curl_setopt($ch, CURLOPT_POST, true);

// Použití http_build_query() pro převod pole $data na URL-kódovaný řetězec
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

$result = curl_exec($ch);
curl_close($ch);

/*
V tomto příkladu http_build_query($data) převede asociativní pole $data na URL-kódovaný řetězec key1=value1&key2=value2&key3=value3.
Tento řetězec je pak použit jako tělo POST požadavku, což je formát, který webové servery obvykle očekávají pro data odeslaná z HTML formulářů.
http_build_query() je velmi užitečná funkce, protože automaticky zajišťuje URL kódování dat, takže nemusíte ručně kódovat každý klíč a hodnotu v poli.
To zjednodušuje práci s daty formulářů a jejich odesílání pomocí cURL v PHP.
*/

//--------------------------




/*
Použití této hlavičky říká serveru, že tělo požadavku je formátováno jako data formuláře a server by měl tato data interpretovat jako taková.
Toto je zvláště důležité, pokud odesíláte data pomocí POST požadavku, protože server musí vědět, jak data ve vašem požadavku dekódovat a zpracovat.
*/
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/x-www-form-urlencoded"
]);

//-----------------------
