<?php
// Imposta l'intestazione HTTP per indicare che la risposta è in formato JSON
header('Content-Type: application/json');

// Verifica se il metodo HTTP è POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // legge il contenuto grezzo del corpo della richiesta HTTP
    $jsonInput = file_get_contents('php://input');
    
    // Decodifica il JSON in un array associativo perchè passo true come secondo parametro
    $postData = json_decode($jsonInput, true);

    // Verifica se il JSON è valido
    if (json_last_error() === JSON_ERROR_NONE) {
        // Formatta i dati ricevuti per la risposta
        $response = [
            "message" => "Dati JSON ricevuti con successo!",
            "status" => "success",
            "receivedData" => $postData, // I dati JSON ricevuti
            "timestamp" => date('Y-m-d H:i:s')
        ];
    } else {
        // JSON non valido
        $response = [
            "message" => "Formato JSON non valido.",
            "status" => "error",
            "timestamp" => date('Y-m-d H:i:s')
        ];
    }
} else {
    // Metodo non consentito
    $response = [
        "message" => "Metodo HTTP non supportato. Usa POST.",
        "status" => "error",
        "timestamp" => date('Y-m-d H:i:s')
    ];
}

// Converte l'array in JSON e lo stampa
echo json_encode($response);
?>
