# Usa un'immagine base di Python
FROM python:3.9-slim

# Imposta la directory di lavoro all'interno del container
WORKDIR /app

# Copia i file del progetto nel container
COPY .  /app

# Installa le dipendenze
RUN pip install --no-cache-dir -r requirements.txt

# Esponi la porta su cui Flask verrà eseguito
EXPOSE 5000

# Comando per avviare l'applicazione
CMD ["python", "main.py"]
