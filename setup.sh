#!/bin/bash

# Создаём образы сервисов
echo "Создаём образы сервисов..."
docker compose build

# Создаём .env на основе .env.example
echo "Создаём .env на основе .env.example..."
docker compose run --rm --no-deps app cp .env.example .env

# Запрашиваем секретные данные для .env
read -p "Введите пароль для базы данных (DB_PASSWORD): " DB_PASSWORD
read -p "Введите HCaptcha sitekey: " HCAPTCHA_SITEKEY
read -p "Введите HCaptcha secret: " HCAPTCHA_SECRET

# Обновляем .env
sed -i "s/DB_PASSWORD=.*/DB_PASSWORD=$DB_PASSWORD/" .env
sed -i "s/HCAPTCHA_SITEKEY=.*/HCAPTCHA_SITEKEY=$HCAPTCHA_SITEKEY/" .env
sed -i "s/HCAPTCHA_SECRET=.*/HCAPTCHA_SECRET=$HCAPTCHA_SECRET/" .env

# Устанавливаем PHP-зависимости
echo "Устанавливаем PHP-зависимости..."
docker compose run --rm --no-deps app composer install

# Устанавливаем JS-зависимости
echo "Устанавливаем JS-зависимости..."
docker compose run --rm --no-deps app npm install

# Генерируем ключ приложения
echo "Генерируем ключ приложения..."
docker compose run --rm --no-deps app php artisan key:generate

# Создаём символическую ссылку с public/storage на storage/app/public
echo "Создаём символическую ссылку с public/storage на storage/app/public..."
docker compose run --rm --no-deps app php artisan storage:link

# Настраиваем базу данных
echo "Создаём таблицы БД и выполняем начальное заполнение данных..."
docker compose run --rm app php artisan migrate:fresh --seed

# Останавливаем контейнеры
docker compose down

echo "Готово! Базовая настройка проекта завершена."
