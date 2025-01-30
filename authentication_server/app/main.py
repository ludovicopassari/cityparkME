from flask import Flask, request, jsonify
from authentication.authenticator import check_first_factor, check_second_factor,register_user
from datetime import datetime
from flask_cors import CORS
import re

app = Flask(__name__)
CORS(app)



@app.route('/authenticate', methods=['POST'])
def authenticate():
    # Funzione per la risposta di errore
    def error_response(message="Invalid request"):
        return jsonify({"status": "error", "message": message}), 400

    # Funzione per validare l'email
    def is_valid_email(email):
        email_regex = r'^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$'
        return re.match(email_regex, email)

    # Ottieni il JSON dalla richiesta
    message = request.get_json()

    # Validazione dell'input
    if not message or 'action' not in message or 'data' not in message:
        return error_response()

    action = message['action']
    data = message['data']

    if action == 'login_request':
        # Estrai email e password
        email = data.get('email')
        passwd = data.get('password')

        # Validazione email e password
        if not email or not passwd:
            return error_response("Email and password are required")
        if not is_valid_email(email):
            return error_response("Invalid email format")

        # Esegui il primo fattore di autenticazione
        return check_first_factor(email, passwd)

    elif action == 'verify_otp':
        # Estrai OTP e transaction_id
        otp = data.get('otp')
        transaction_id = data.get('transaction_id')

        if not otp or not transaction_id:
            return error_response("OTP and transaction ID are required")

        # Esegui il secondo fattore di autenticazione
        return jsonify(check_second_factor(otp, transaction_id))

    # Azione non valida
    return error_response("Invalid action")


@app.route('/register', methods=['POST'])
def register():

    # Messaggio di errore generico
    response_error = {
        "status": "error",
        "message": "Invalid request"
    }

    try:
        # Ottieni il JSON dalla richiesta
        message = request.get_json()
        
        if not message:
            response_error['message'] = "Request body must be JSON."
            return jsonify(response_error), 400
        
        # Validazione dell'azione
        action = message.get('action')
        if action != "register_request":
            response_error['message'] = "Invalid action."
            return jsonify(response_error), 400

        # Ottieni i dati
        data = message.get('data', {})
        if not data:
            response_error['message'] = "Data is missing."
            return jsonify(response_error), 400

        # Validazione dei campi
        first_name = data.get('first_name')
        last_name = data.get('last_name')
        email = data.get('email')
        username = data.get('username')
        password = data.get('password')

        if not first_name or not last_name or  not email or not password:
            response_error['message'] = "Incomplete data..."
            return jsonify(response_error), 400

        # Logica di registrazione (es. salvataggio nel database)
        response_success = register_user(first_name, last_name,username,email,password)

        return jsonify(response_success)

    except Exception as e:
        # Gestione degli errori generici
        response_error['message'] = f"An error occurred: {str(e)}"
        return jsonify(response_error), 500

    # Aggiungi un return di fallback, anche se non dovrebbe mai essere raggiunto
    return jsonify(response_error), 400

# Avvia il server
if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5000)
