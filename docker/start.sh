#!/bin/bash

# Ждем, пока база данных будет готова
echo "Waiting for database..."
while ! php artisan db:monitor --timeout=1 > /dev/null 2>&1; do
    sleep 1
done
echo "Database is ready!"

# Очищаем кэш конфигурации
php artisan config:clear

# Запускаем миграции
echo "Running migrations..."
php artisan migrate --force

# Очищаем все кэши
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# Оптимизируем приложение
php artisan optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Запускаем приложение
php -S 0.0.0.0:8000 -t public 