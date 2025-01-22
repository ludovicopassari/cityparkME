<?php
try {
    // Connessione al database con PDO
    $dsn = "mysql:host=mysql;dbname=my_database;charset=utf8mb4";
    $username = "root";
    $password = "rootpassword";

    // Creazione dell'istanza PDO
    $pdo = new PDO($dsn, $username, $password);

    // Imposta la modalità di errore PDO su eccezione
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Query di aggiornamento
    $query = "UPDATE reservations SET status = 'completed' WHERE end_time < NOW()";

    // Esecuzione della query
    $stmt = $pdo->prepare($query);
    $stmt->execute();

    echo "Updated successfully. Rows affected: " . $stmt->rowCount();

} catch (PDOException $e) {
    // Gestione degli errori
    echo "Error: " . $e->getMessage();
}

// Chiusura della connessione (opzionale, avviene automaticamente)
$pdo = null;
?>
