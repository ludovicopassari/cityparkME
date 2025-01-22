import random
import mysql.connector
from datetime import datetime
import time

# Configurazione del database
db_config = {
    'host': 'localhost',
    'user': 'root',
    'password': 'rootpassword',
    'database': 'my_database',
    'port': 3333
}

def connect_db():
    return mysql.connector.connect(**db_config)

def create_parking_slots(num_slots, area, rate_range):
    """
    Crea stalli di parcheggio in base a un'area geografica realistica.
    """
    connection = connect_db()
    cursor = connection.cursor()

    # Inizializzare l'ID dello stallo
    slot_id = 1

    for lat, lon in area:
        if slot_id > num_slots:
            break

        location = f"Stallo {slot_id}"
        rate_per_hour = round(random.uniform(*rate_range), 2)

        query = (
            "INSERT INTO parking_slots (location, latitude, longitude, rate_per_hour) "
            "VALUES (%s, %s, %s, %s)"
        )
        cursor.execute(query, (location, lat, lon, rate_per_hour))

        slot_id += 1

    connection.commit()
    cursor.close()
    connection.close()

def simulate_occupancy(num_updates):
    """
    Simula l'occupazione degli stalli basata su orari e giorni realistici.
    """
    connection = connect_db()
    cursor = connection.cursor()

    for _ in range(num_updates):
        # Scegli uno stallo casuale
        query = "SELECT slot_id FROM parking_slots ORDER BY RAND() LIMIT 1"
        cursor.execute(query)
        slot_id = cursor.fetchone()[0]

        # Simula l'occupazione con maggiore realismo
        hour_of_day = datetime.now().hour
        weekday = datetime.now().weekday()

        # Occupazione in base all'ora del giorno e al giorno della settimana
        if 8 <= hour_of_day <= 18 and weekday < 5:  # Orario lavorativo (8-18) dal lunedì al venerdì
            is_occupied = random.choice([True, False, True])  # Più probabilità di essere occupato
        else:  # Orari serali o weekend
            is_occupied = random.choice([True, False])

        # Aggiorna lo stato di occupazione
        query = "UPDATE parking_slots SET is_occupied = %s WHERE slot_id = %s"
        cursor.execute(query, (is_occupied, slot_id))

    connection.commit()
    cursor.close()
    connection.close()

# Esegui lo script
if __name__ == "__main__":
    num_slots = 100
    area = [
        (38.086071612510594 + 0.001 * i, 15.498210773835222 + 0.001 * j)
        for i in range(-5, 5)  # Latitudine variabile su una piccola area
        for j in range(-5, 5)  # Longitudine variabile su una piccola area
    ]
    rate_range = (1.0, 5.0)  # Tariffe orarie tra 1.0 e 5.0 euro

    print("Generazione degli stalli di parcheggio...")
    create_parking_slots(num_slots, area, rate_range)
    print("Stalli generati con successo!")

    print("Simulazione dello stato di occupazione...")
    simulate_occupancy(50)  # Aggiorna lo stato di 50 stalli casuali
    print("Simulazione completata!")
