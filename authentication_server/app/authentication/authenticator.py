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

SECRET_KEY = "la_tua_chiave_segreta"

def generate_transaction_id():
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

sender_email = "mecitypark@gmail.com" 
sender_password = "kmbn pdki kpsy nmau"  
smtp_server = "smtp.gmail.com"  
smtp_port = 587

def send_email(sender_email, sender_password, recipient_email, smtp_server, smtp_port, verification_code):
    """Invia un'email contenente il codice di verifica."""
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
    """Genera un codice di verifica casuale."""
    return ''.join(random.choices(string.ascii_uppercase + string.digits, k=length))

def check_first_factor(prop_email, prop_passwd):
  
    try:

        connection = mysql.connector.connect(**DB_CONFIG)

        cursor = connection.cursor(dictionary=True)

        user_query = "SELECT id,email,password_hash,is_2fa_enabled FROM users WHERE email = %s "

        cursor.execute(user_query, (prop_email,))
        
        user_data = cursor.fetchone()

        if user_data:
            user_id = user_data.get('id')
            email = user_data.get('email')
            passwd_hash = user_data.get('password_hash')
            two_factor_required = user_data.get('is_2fa_enabled')

            if email == prop_email and passwd_hash == prop_passwd and  two_factor_required == 0:
                # Creazione del payload
                payload = {
                    "user_id": user_id,
                    "email": email,
                    "exp": datetime.datetime.utcnow() + datetime.timedelta(hours=1)  # Scadenza: 1 ora
                }

                # Generazione del token
                token = jwt.encode(payload, SECRET_KEY, algorithm="HS256")
                response_a = {
                    "status": "success",
                    "message": "Authentication successful",
                    "auth_token": token
                }

                return response_a
            
            elif email == prop_email and passwd_hash == prop_passwd and two_factor_required == 1:
                
                
                otp_code = generate_verification_code()
                transaction_id = str(generate_transaction_id())

                # prepara la query per inserire OTP nel DB
                sql_insert_query = "INSERT INTO otp_requests (transaction_id,user_id, otp_code, expiration_time, is_used, created_at) VALUES (%s,%s, %s, %s, %s, NOW());"
                
                current_time = datetime.datetime.now()

                expiration_time = current_time + timedelta(minutes=5)

                expiration_time_str = expiration_time.strftime('%Y-%m-%d %H:%M:%S')

                values = (transaction_id, user_id, otp_code, expiration_time_str, False) 

                

                cursor.execute(sql_insert_query, values)
                connection.commit()

                send_email(sender_email, sender_password, email, smtp_server, smtp_port, otp_code)
                response = {
                    "status": "pending",
                    "message": "OTP sent successfully",
                    "otp_channel": "email",
                    "transaction_id": transaction_id
                }

                return response
            else:
                err = {
                    "status": "error",
                    "message": "Invalid username or password",
                }

                return err


    except Error as e:
        err = {
            "status": transaction_id,
            "message": "generic error",
        }
        return err
    finally:
        # Chiudi la connessione
        if 'connection' in locals() and connection.is_connected():
            cursor.close()
            connection.close()
           
def check_second_factor(otp_prop,id_transaction):

    try:

        connection = mysql.connector.connect(**DB_CONFIG)

        cursor = connection.cursor(dictionary=True)

        user_query =  "SELECT user_id, otp_code, expiration_time FROM otp_requests WHERE transaction_id = %s"

        cursor.execute(user_query, (id_transaction,))
        
        transaction_data = cursor.fetchone()

        if transaction_data:
            #verificare che otp sia corretto
            otp = transaction_data.get('otp_code')
            expire_time = datetime.datetime.strptime(str(transaction_data.get('expiration_time')), "%Y-%m-%d %H:%M:%S")
            now = datetime.datetime.now()
            user_id = transaction_data.get('user_id')

            if now > expire_time:
                expire_otp_err = {
                    "status": "error",
                    "message": "Invalid or expired OTP",
                }
                return expire_otp_err
            else:
                if otp_prop == otp:
                      # Creazione del payload
                    payload = {
                        "user_id": user_id,
                        "exp": datetime.datetime.utcnow() + datetime.timedelta(hours=0.0333)  
                    }

                    # Generazione del token
                    token = jwt.encode(payload, SECRET_KEY, algorithm="HS256")
                    response_a = {
                        "status": "success",
                        "message": "Authentication successful",
                        "auth_token": token
                    }

                    return response_a

        
        err = {
            "status": "error",
            "message": "generic error",
        }
        return err
        


    except Error as e:
        err = {
            "status": "error",
            "message": "generic error",
        }
        return err
    finally:
        # Chiudi la connessione
        if 'connection' in locals() and connection.is_connected():
            cursor.close()
            connection.close()

def register_user(first_name,last_name,username,email,passwd):
    try:

        connection = mysql.connector.connect(**DB_CONFIG)

        cursor = connection.cursor(dictionary=True)

        user_query =  "INSERT INTO users (email,password_hash, is_2fa_enabled, created_at, updated_at) VALUES (%s,%s,%s,NOW(),NOW())"

        cursor.execute(user_query, (email,passwd,1))
        
        connection.commit()

        response_success = {
            "status": "success",
            "message": "Registration completed successfully",
        }

        return response_success

    except Error as e:
        err = {
            "status": "error",
            "message": "generic error",
        }
        return err
    finally:
        # Chiudi la connessione
        if 'connection' in locals() and connection.is_connected():
            cursor.close()
            connection.close()
      
        