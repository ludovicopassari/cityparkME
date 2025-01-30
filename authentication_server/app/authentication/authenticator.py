import mysql.connector
from mysql.connector import Error
from .config import DB_CONFIG
import random
import string
from datetime import datetime, timedelta
from email.mime.text import MIMEText
from email.mime.multipart import MIMEMultipart
import smtplib
import jwt
import hashlib
import time
import os
import datetime
from datetime import  timedelta
import bcrypt
import os

SECRET_KEY = "la_tua_chiave_segreta"

sender_email = "mecitypark@gmail.com" 
sender_password = "kmbn pdki kpsy nmau"  
smtp_server = "smtp.gmail.com"  
smtp_port = 587

def generate_transaction_id():
    """
    Generates a unique transaction ID.
    This function creates a unique transaction ID by combining the current timestamp
    with a random value, hashing the combination using SHA-256, and then taking the
    first 6 bytes of the hash. The result is converted to a readable hexadecimal format.
    Returns:
        str: A unique transaction ID in hexadecimal format.
    """
    # 1. Genera un sale basato sul timestamp corrente
    salt = str(time.time()).encode()  # Timestamp in formato stringa e codificato in byte
    
    # 2. Aggiungi un valore casuale per garantire unicità
    random_value = os.urandom(16)  # Genera 16 byte casuali
    
    # 3. Combina il sale con il valore casuale
    combined = salt + random_value
    
    # 4. Calcola l'hash (SHA-256)
    hash_object = hashlib.sha256(combined)
    
    # 5. Prendi i primi 6 byte dell'hash
    transaction_id = hash_object.digest()[:6]  # Ottieni i primi 6 byte
    
    # 6. Converti in formato leggibile (esadecimale)
    return transaction_id.hex()


def send_email(sender_email, sender_password, recipient_email, smtp_server, smtp_port, verification_code):
    """
    Sends an email with a verification code to the specified recipient.
    Args:
        sender_email (str): The email address of the sender.
        sender_password (str): The password of the sender's email account.
        recipient_email (str): The email address of the recipient.
        smtp_server (str): The SMTP server address.
        smtp_port (int): The port number for the SMTP server.
        verification_code (str): The verification code to be sent in the email.
    Raises:
        Exception: If there is an error in sending the email.
    """
    
    try:
        # Creazione del messaggio email
        message = MIMEMultipart()
        message["From"] = sender_email
        message["To"] = recipient_email
        message["Subject"] = "Codice di verifica"

        body = f"Il tuo codice di verifica è: {verification_code}"
        message.attach(MIMEText(body, "plain"))

        # Connessione al server SMTP
        with smtplib.SMTP(smtp_server, smtp_port) as server:
            server.starttls()  # Avvia la connessione sicura
            server.login(sender_email, sender_password)
            server.send_message(message)

        print("Email inviata con successo!")
    except Exception as e:
        print(f"Errore nell'invio dell'email: {e}")


def generate_verification_code(length=6):
    """
    Generate a random verification code.

    This function generates a random verification code consisting of uppercase 
    letters and digits. The default length of the verification code is 6 characters, 
    but this can be customized by providing a different length.

    Args:
        length (int, optional): The length of the verification code. Defaults to 6.

    Returns:
        str: A randomly generated verification code.
    """
    return ''.join(random.choices(string.ascii_uppercase + string.digits, k=length))


def check_first_factor(email, password):
    try:
        # Connessione al database
        connection = mysql.connector.connect(**DB_CONFIG)
        cursor = connection.cursor(dictionary=True)

        # Recupera i dati dell'utente
        user_query = "SELECT id, email, password_hash, salt, two_factor_enabled FROM users WHERE email = %s"
        cursor.execute(user_query, (email,))
        user_data = cursor.fetchone()

        if not user_data:
            return {
                "status": "error",
                "message": "Invalid email or password",
            }

        # Estrai i dati
        stored_hash = user_data['password_hash']
        salt = user_data['salt']
        two_factor_required = user_data['two_factor_enabled']

        # Verifica la password
        salted_password = (password + salt).encode('utf-8')
        is_valid = bcrypt.checkpw(salted_password, stored_hash.encode('utf-8'))

        if not is_valid:
            return {
                "status": "error",
                "message": "Invalid email or password",
            }

        # Se non è richiesta 2FA, restituisci un token JWT
        if not two_factor_required:
            payload = {
                "user_id": user_data['id'],
                "email": user_data['email'],
                "exp": datetime.datetime.utcnow() + datetime.timedelta(hours=1),
            }
            token = jwt.encode(payload, SECRET_KEY, algorithm="HS256")
            return {
                "status": "success",
                "message": "Authentication successful",
                "auth_token": token,
            }

        # Se è richiesta 2FA, procedi con l'invio del codice OTP
        otp_code = generate_verification_code()
        transaction_id = str(generate_transaction_id())
        insert_otp_query = """
            INSERT INTO otp_transactions (transaction_id, user_id, otp_code, expiration_time, is_used, created_at) 
            VALUES (%s, %s, %s, %s, %s, NOW())
        """
        expiration_time = datetime.datetime.now() + datetime.timedelta(minutes=5)
        cursor.execute(insert_otp_query, (transaction_id, user_data['id'], otp_code, expiration_time, False))
        connection.commit()

        send_email(sender_email, sender_password, email, smtp_server, smtp_port, otp_code)

        return {
            "status": "pending",
            "message": "OTP sent successfully",
            "transaction_id": transaction_id,
        }

    except mysql.connector.Error as db_error:
        return {
            "status": "error",
            "message": "Database error occurred.",
            "details": str(db_error),  # Facoltativo, da rimuovere in produzione
        }

    except Exception as e:
        return {
            "status": "error",
            "message": "An unexpected error occurred.",
        }

    finally:
        if 'connection' in locals() and connection.is_connected():
            cursor.close()
            connection.close()


           
def check_second_factor(otp_prop, id_transaction):
    try:
        # Connessione al database
        connection = mysql.connector.connect(**DB_CONFIG)
        cursor = connection.cursor(dictionary=True)

        # Query per ottenere i dati della transazione OTP
        user_query = """
            SELECT user_id, otp_code, expiration_time, is_used 
            FROM otp_transactions 
            WHERE transaction_id = %s
        """
        cursor.execute(user_query, (id_transaction,))
        transaction_data = cursor.fetchone()

        if not transaction_data:
            return {
                "status": "error",
                "message": "Invalid transaction ID or OTP",
            }

        # Estrazione dei dati della transazione
        otp = transaction_data.get('otp_code')
        expire_time = datetime.datetime.strptime(str(transaction_data.get('expiration_time')), "%Y-%m-%d %H:%M:%S")
        now = datetime.datetime.now()
        user_id = transaction_data.get('user_id')
        is_used = transaction_data.get('is_used')

        # Validazione OTP: scadenza o già usato
        if now > expire_time or is_used:
            return {
                "status": "error",
                "message": "Invalid or expired OTP",
            }

        # Verifica OTP
        if otp_prop != otp:
            return {
                "status": "error",
                "message": "Incorrect OTP",
            }

        # Se OTP è corretto, contrassegnalo come utilizzato
        update_query = "UPDATE otp_transactions SET is_used = %s WHERE transaction_id = %s"
        cursor.execute(update_query, (1, id_transaction))
        connection.commit()

        # Creazione del payload per il token JWT
        payload = {
            "user_id": user_id,
            "exp": datetime.datetime.utcnow() + datetime.timedelta(hours=1),  # Token valido per 1 ora
        }
        token = jwt.encode(payload, SECRET_KEY, algorithm="HS256")

        return {
            "status": "success",
            "message": "Authentication successful",
            "auth_token": token,
        }

    except mysql.connector.Error as db_error:
        # Gestione degli errori del database
        return {
            "status": "error",
            "message": "Database error occurred.",
            "details": str(db_error),  # Facoltativo: rimuovere per maggiore sicurezza
        }

    except Exception as e:
        # Gestione degli errori generici
        return {
            "status": "error",
            "message": "An unexpected error occurred.",
        }

    finally:
        # Chiusura della connessione al database
        if 'connection' in locals() and connection.is_connected():
            cursor.close()
            connection.close()


def register_user(first_name, last_name, username, email, password):
    try:
        # Connessione al database
        connection = mysql.connector.connect(**DB_CONFIG)
        cursor = connection.cursor(dictionary=True)

        # Controlla se l'email o lo username esistono già
        check_query = "SELECT COUNT(*) AS count FROM users WHERE email = %s OR username = %s"
        cursor.execute(check_query, (email, username))
        result = cursor.fetchone()

        if result['count'] > 0:
            return {
                "status": "error",
                "message": "Email or username already in use.",
            }

        # Genera un salt unico
        salt = bcrypt.gensalt().decode('utf-8')  # Genera un salt in formato leggibile

        # Hash della password con il salt
        salted_password = (password + salt).encode('utf-8')
        hashed_password = bcrypt.hashpw(salted_password, bcrypt.gensalt()).decode('utf-8')

        # Query per inserire un nuovo utente
        user_query = """
            INSERT INTO users 
            (first_name, last_name, username, email, password_hash, salt, two_factor_enabled, created_at, updated_at) 
            VALUES (%s, %s, %s, %s, %s, %s, %s, NOW(), NOW())
        """
        cursor.execute(user_query, (first_name, last_name, username, email, hashed_password, salt, 1))
        connection.commit()

        # Risposta di successo
        return {
            "status": "success",
            "message": "Registration completed successfully",
        }

    except mysql.connector.Error as db_error:
        # Gestione degli errori del database
        return {
            "status": "error",
            "message": "Database error occurred.",
            "details": str(db_error),  # Facoltativo, da rimuovere in produzione
        }

    except Exception as e:
        # Gestione degli errori generici
        return {
            "status": "error",
            "message": "An unexpected error occurred.",
        }

    finally:
        # Chiusura della connessione al database
        if 'connection' in locals() and connection.is_connected():
            cursor.close()
            connection.close()