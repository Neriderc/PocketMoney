# React build stage
FROM node:18 AS react-build
WORKDIR /app

# Install dependencies and build
COPY ./frontend/package.json ./frontend/package-lock.json ./
RUN npm install
COPY ./frontend ./
RUN npm run build

# Symfony build stage
FROM php:8.3-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libsqlite3-dev \
    unzip \
    git \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_sqlite

# Environment configuration
ARG APP_ENV=prod
ENV APP_ENV=$APP_ENV

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Set working directory
WORKDIR /var/www/backend

# Copy backend (Symfony) app
COPY ./backend ./ 

# Copy built React frontend to Symfony public directory
COPY --from=react-build /app/dist ./public/

RUN mkdir -p var/db && \
    touch var/db/data.db && \
    chown -R www-data:www-data var/db && \
    chmod -R 775 var/db

# Install Symfony dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Fix permissions
RUN chown -R www-data:www-data var

# Expose the port that Nginx will be listening on (port 80)
EXPOSE 80

# Copy entrypoint script
COPY ./backend/docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh

# Make it executable
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Set the entrypoint
ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]

# Set the default command to run PHP-FPM
CMD ["php-fpm"]

