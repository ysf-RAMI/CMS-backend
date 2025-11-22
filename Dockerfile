# ---------------------------------------------
# 1. Use official PHP image with extensions
# ---------------------------------------------
FROM php:8.3-fpm

# 2. Install system dependencies and PHP extensions
RUN apt-get update \
	&& apt-get install -y \
		git \
		curl \
		libpng-dev \
		libonig-dev \
		libxml2-dev \
		zip \
		unzip \
	&& docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# 3. Install Composer
COPY --from=composer:2.5 /usr/bin/composer /usr/bin/composer

# 4. Set working directory
WORKDIR /var/www

# 5. Copy application code
COPY . .

# 6. Set permissions for Laravel
RUN chown -R www-data:www-data /var/www \
	&& chmod -R 755 /var/www/storage

# 7. Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# 8. Expose port 8000 (default for Laravel's built-in server)
EXPOSE 8000

# 9. Set environment variables (optional, for production)
ENV APP_ENV=production
ENV APP_DEBUG=false

# 10. Start Laravel using PHP's built-in server
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]

# ---
# How this works:
# - The image is based on PHP-FPM, suitable for production.
# - Installs all required PHP extensions for Laravel.
# - Installs Composer for dependency management.
# - Copies your code and sets permissions for storage.
# - Installs Composer dependencies optimized for production.
# - Exposes port 8000 and starts the Laravel app.
#
# On Render, you may also want to set up a Procfile with:
# web: php artisan serve --host 0.0.0.0 --port 8000
