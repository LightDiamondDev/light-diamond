<p style="text-align: center">
  <picture>
    <source media="(prefers-color-scheme: dark)" srcset="/public/images/logo-dark.svg?raw=true">
    <img src="/public/images/logo.svg?raw=true" width="400" alt="Light Diamond Logo">
  </picture>
</p>

## Установка

Для начальной настройки проекта следует выполнить следующие шаги.

1. Убедитесь, что установлена актуальная версия Docker.
2. Скачайте репозиторий, распакуйте, перейдите в корень проекта;
3. Запустите скрипт `setup.sh` и дождитесь окончания его выполнения;
4. Сконфигурируйте MySQL пароли в файле `.env`, установив значения для переменных `DB_ROOT_PASSWORD` и `DB_PASSWORD`;
5. Запустите скрипт `setup-db.sh` и дождитесь окончания его выполнения;

Запуск приложения осуществляется с помощью команды `docker compose up -d`.

Для остановки приложения используется команда `docker compose down`.
