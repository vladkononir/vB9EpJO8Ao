# StoryValut

![Yii2](https://img.shields.io/badge/Yii2-2.0.48-blue)
![PHP](https://img.shields.io/badge/PHP-8.1-green)
![MySQL](https://img.shields.io/badge/MySQL-8.0-orange)
![Docker](https://img.shields.io/badge/Docker-✓-blue)

## 🕒 Затраченное время на выполнение
Общее время разработки: ~ 12-13 часов

## Описание

StoryValut - это веб-приложение, где любой пользователь может оставить своё сообщение.
Приложение разработано на Yii2 с использованием Docker.

## 📋 Требования

- Docker
- Docker Compose

## 🚀 Быстрый старт

```bash
# Клонировать репозиторий
git clone https://github.com/vladkononir/vB9EpJO8Ao.git
cd vB9EpJO8Ao

# Запуск приложения
make init

# Или вручную:
docker-compose up -d
docker-compose exec php composer install
docker-compose exec php php yii migrate

# Открыть в браузере
# http://localhost:8080
