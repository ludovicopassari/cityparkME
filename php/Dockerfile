# Usa l'immagine ufficiale PHP con FPM
FROM php:8.2-fpm

# Installa estensioni PHP necessarie (ad esempio, mysqli, pdo)
RUN docker-php-ext-install mysqli pdo pdo_mysql
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Aggiorna i pacchetti e installa zip e git
RUN apt-get update && apt-get install -y \
    zip \
    git \
    libzip-dev \
    && docker-php-ext-install zip

# Installa cron
RUN apt-get update && apt-get install -y cron

# Copia il file crontab personalizzato
COPY crontab /etc/cron.d/mycron

# Imposta i permessi corretti per il file crontab
RUN chmod 0644 /etc/cron.d/mycron



# Aggiungi il comando per eseguire il cron daemon
RUN touch /var/log/cron.log

# Imposta la directory di lavoro
WORKDIR /var/www/html

# Copia i file del progetto nel container
COPY .. /var/www/html
RUN chmod 777 /var/www/html/script.php
# Espone la porta 9000 per PHP-FPM
EXPOSE 9000

# Comando per avviare cron e PHP-FPM
CMD cron && tail -f /var/log/cron.log & php-fpm
