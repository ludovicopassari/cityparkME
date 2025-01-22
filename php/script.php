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

    // 1. Query per selezionare gli ID delle righe che saranno aggiornate
    $query = "SELECT reservation_id, slot_id FROM reservations WHERE end_time < NOW() AND status != 'completed'";

    // Esecuzione della query per ottenere gli ID
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    
    $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $reservationIds = array_column($reservations, 'reservation_id');
    $slot_ids = array_column($reservations, 'slot_id');

     // Verifica se ci sono righe da aggiornare
    if (count($reservationIds) > 0) {
        // 2. Query per aggiornare lo stato delle prenotazioni
        $updateQuery = "UPDATE reservations SET status = 'completed' WHERE reservation_id = :reservation_id";

        // Esegui l'aggiornamento per ogni ID
        $updateStmt = $pdo->prepare($updateQuery);

        foreach ($reservationIds as $reservationId) {
            // Esegui l'aggiornamento per ogni ID
            $updateStmt->bindParam(':reservation_id', $reservationId, PDO::PARAM_INT);
            $updateStmt->execute();
        }

        echo "Updated successfully. Reservations affected: " . count($reservationIds);

       
        // Prepara la query per aggiornare lo stato di occupazione degli slot
        $updateQuery = "UPDATE parking_slots SET is_occupied = 0 WHERE slot_id = :slot_id";
    
        // Esegui l'aggiornamento per ogni slot
        $updateStmt = $pdo->prepare($updateQuery);
    
        foreach ($slot_ids as $slotId) {
            // Esegui l'aggiornamento per ogni slot ID
            $updateStmt->bindParam(':slot_id', $slotId, PDO::PARAM_INT);
            $updateStmt->execute();
        }
    
            echo "Updated successfully. Slots affected: " . count($slotIds);
        } else {
            echo "No completed reservations found.";
        }
            

} catch (PDOException $e) {
    // Gestione degli errori
    echo "Error: " . $e->getMessage();
}

// Chiusura della connessione (opzionale, avviene automaticamente)
$pdo = null;
?>