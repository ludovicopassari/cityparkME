<?php


// Permetti tutte le origini (o specifica solo una particolare origine se preferisci)
header("Access-Control-Allow-Origin: *");

// Permetti i metodi che possono essere utilizzati (GET, POST, PUT, DELETE, etc.)
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE");

// Permetti le intestazioni personalizzate
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Se la richiesta è un preflight (OPTIONS), ritorna una risposta vuota
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}



require_once __DIR__ . '/../vendor/autoload.php'; // Assumendo che il file si trovi nella cartella superiore

use \Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Includi il file di configurazione
$config = include './config.php';

// Usa i parametri di configurazione
$host = $config['host'];
$dbname = $config['dbname'];
$username = $config['username'];
$password = $config['password'];
$charset = 'utf8mb4';
$secret_key = $config['secret_key'];  

// Connessione al database con PDO
$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

// Impostiamo l'intestazione per il tipo di contenuto JSON
header('Content-Type: application/json');


// Verifica se la richiesta è una POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Leggi il token JWT dall'header Authorization
    $auth_header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    $bearer_token = null;

    // Estrai il token JWT dal header Authorization
    if (preg_match('/Bearer\s(\S+)/', $auth_header, $matches)) {
        $bearer_token = $matches[1];
    }

    if($bearer_token){


        try{
                // Decodifica il token JWT
                $decoded = JWT::decode($bearer_token, new Key($secret_key, 'HS256'));
                // Leggi il corpo della richiesta JSON
                $input = file_get_contents('php://input');
                $data = json_decode($input, true);

                // Controlla se il campo id_user esiste nel JSON
                if (isset($data['id_user'])) {
                    $id_user = $data['id_user'];

                    // Prepara la query per recuperare i dati in base a id_user
                    $sql = "SELECT * FROM credit_cards WHERE user_id = :id_user";
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindParam(':id_user', $id_user, PDO::PARAM_INT);

                    // Esegui la query
                    $stmt->execute();
                    $result = $stmt->fetchall();

                    // Controlla se sono stati trovati risultati
                    if ($result) {
                        echo json_encode(['success' => true, 'data' => $result]);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Utente non trovato']);
                    }
                } else {
                    // Se id_user non è presente nel body JSON
                    echo json_encode(['success' => false, 'message' => 'Campo id_user mancante']);
                }
        }catch (Exception $e) {
            // Token non valido o scaduto
            http_response_code(401); // Non autorizzato
            echo json_encode(['success' => false, 'message' => 'Token non valido o scaduto']);
        }
    }else {
        // Se il token non è presente nell'header Authorization
        http_response_code(401); // Non autorizzato
        echo json_encode(['success' => false, 'message' => 'Token mancante']);
    }

    
} else {
    // Se la richiesta non è una POST
    echo json_encode(['success' => false, 'message' => 'Metodo non consentito']);
}



?>