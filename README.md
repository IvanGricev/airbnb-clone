# Airbnb Clone

## Описание проекта
Этот проект представляет собой клон популярного сервиса Airbnb, разработанный с использованием Laravel (PHP) и современных веб-технологий. Проект включает в себя основные функции бронирования жилья, управления пользователями и листингами.

## Требования
- PHP 8.1 или выше
- Composer
- Node.js и npm
- Docker и Docker Compose (опционально)
- MySQL 8.0 или выше

## Установка и настройка

### 1. Клонирование репозитория
```bash
git clone [URL вашего репозитория]
cd airbnb-clone
```

### 2. Установка зависимостей PHP
```bash
composer install
```

### 3. Установка зависимостей Node.js
```bash
npm install
```

### 4. Настройка окружения
```bash
cp .env.example .env
php artisan key:generate
```

### 5. Настройка базы данных
1. Создайте базу данных MySQL
2. Отредактируйте файл `.env` и укажите параметры подключения к базе данных:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=airbnb_clone
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 6. Миграции и сиды
```bash
php artisan migrate
php artisan db:seed
```

### 7. Сборка фронтенд-ресурсов
```bash
npm run build
```

## Запуск проекта

### Локальный запуск
```bash
php artisan serve
```

### Запуск через Docker
```bash
docker-compose up -d
```

После запуска проект будет доступен по адресу: http://localhost:8000

## Дополнительные команды

### Очистка кэша
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

## Структура проекта
- `/app` - Основной код приложения
- `/config` - Конфигурационные файлы
- `/database` - Миграции и сиды
- `/public` - Публичные файлы
- `/resources` - Фронтенд-ресурсы
- `/routes` - Маршруты приложения
- `/storage` - Файлы хранилища

## Лицензия
MIT
