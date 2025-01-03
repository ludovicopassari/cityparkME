# Usa l'immagine ufficiale PHP con FPM
FROM php:8.2-fpm

# Installa estensioni PHP necessarie (ad esempio, mysqli, pdo)
RUN docker-php-ext-install mysqli pdo pdo_mysql
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Imposta la directory di lavoro
WORKDIR /var/www/html

# Copia i file del progetto nel container
COPY .. /var/www/html

# Espone la porta 9000 per PHP-FPM
EXPOSE 9000