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

# Install system dependencies, including nginx and supervisor
RUN apt-get update && apt-get install -y \
    libpng-dev libjpeg-dev libfreetype6-dev \
    libsqlite3-dev unzip git nginx supervisor \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_sqlite

# Environment configuration
ARG APP_ENV=prod
ENV APP_ENV=$APP_ENV

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Set working directory for Symfony backend
WORKDIR /var/www/backend

# Copy the Symfony backend files
COPY ./backend ./

# Copy built React frontend to Symfony public directory
COPY --from=react-build /app/dist ./public/

# Delete default nginx config
RUN rm /etc/nginx/sites-enabled/default || true

# Copy nginx configuration
COPY ./nginx/nginx.conf /etc/nginx/conf.d/default.conf

# Setup the DB directory and file
RUN mkdir -p var/db && \
    touch var/db/data.db && \
    chown -R www-data:www-data var/db && \
    chmod -R 775 var/db

# Install Symfony dependencies
RUN if [ "$APP_ENV" = "dev" ]; then \
        composer install --no-interaction; \
    else \
        composer install --no-dev --optimize-autoloader --no-interaction; \
    fi

# Fix permissions
RUN chown -R www-data:www-data var

# Copy entrypoint script
COPY ./backend/docker-entrypoint.sh docker-entrypoint.sh
RUN chmod +x docker-entrypoint.sh

# Copy Supervisor configuration to run PHP-FPM and Nginx
COPY ./backend/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Expose port 80 for Nginx
EXPOSE 80

# Set entrypoint to the custom script
ENTRYPOINT ["/var/www/backend/docker-entrypoint.sh"]

# Default command to run supervisord (to manage both PHP-FPM and Nginx)
CMD ["supervisord", "-n"]
