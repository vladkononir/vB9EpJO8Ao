# Переменные проекта
PROJECT_NAME = storyvalut
COMPOSE = docker-compose
EXEC = $(COMPOSE) exec
PHP = $(EXEC) php
DB = $(EXEC) mysql

# Цвета для вывода
GREEN = \033[0;32m
YELLOW = \033[1;33m
RED = \033[0;31m
NC = \033[0m

.PHONY: help up down build restart logs clean bash composer yii migrate init test backup

# Целевая команда по умолчанию
help:
	@echo "$(YELLOW)StoryValut - Управление Docker$(NC)"
	@echo ""
	@echo "$(GREEN)Доступные команды:$(NC)"
	@echo "  $(YELLOW)Разработка:$(NC)"
	@echo "    make up           - Запустить все контейнеры"
	@echo "    make up-build     - Запустить с пересборкой"
	@echo "    make down         - Остановить все контейнеры"
	@echo "    make restart      - Перезапустить контейнеры"
	@echo "    make logs         - Показать логи (режим слежения)"
	@echo "    make logs-php     - Показать логи PHP"
	@echo "    make logs-nginx   - Показать логи Nginx"
	@echo "    make logs-mysql   - Показать логи MySQL"
	@echo ""
	@echo "  $(YELLOW)Управление контейнерами:$(NC)"
	@echo "    make build        - Собрать образы"
	@echo "    make rebuild      - Принудительная пересборка образов"
	@echo "    make clean        - Остановить и удалить контейнеры"
	@echo "    make clean-all    - Полная очистка (контейнеры, образы, volumes)"
	@echo "    make ps           - Показать статус контейнеров"
	@echo ""
	@echo "  $(YELLOW)Приложение:$(NC)"
	@echo "    make bash         - Войти в PHP контейнер"
	@echo "    make bash-mysql   - Войти в MySQL контейнер"
	@echo "    make init         - Полная инициализация проекта"
	@echo "    make composer-install - Установить PHP зависимости"
	@echo "    make migrate      - Выполнить миграции базы данных"
	@echo "    make test         - Запустить тесты"
	@echo ""
	@echo "  $(YELLOW)База данных:$(NC)"
	@echo "    make db-backup    - Создать резервную копию базы данных"
	@echo "    make db-restore   - Восстановить базу данных (использование: make db-restore file=backup.sql)"
	@echo "    make db-shell     - MySQL консоль"
	@echo ""

# Разработка
up:
	@echo "$(GREEN)Запуск контейнеров StoryValut...$(NC)"
	$(COMPOSE) up -d

up-build:
	@echo "$(GREEN)Сборка и запуск контейнеров...$(NC)"
	$(COMPOSE) up -d --build

down:
	@echo "$(YELLOW)Остановка контейнеров...$(NC)"
	$(COMPOSE) down

restart:
	@echo "$(YELLOW)Перезапуск контейнеров...$(NC)"
	$(COMPOSE) restart

# Логи
logs:
	$(COMPOSE) logs -f

logs-php:
	$(COMPOSE) logs -f php

logs-nginx:
	$(COMPOSE) logs -f nginx

logs-mysql:
	$(COMPOSE) logs -f mysql

# Управление контейнерами
build:
	@echo "$(GREEN)Сборка Docker образов...$(NC)"
	$(COMPOSE) build

rebuild:
	@echo "$(GREEN)Принудительная пересборка образов...$(NC)"
	$(COMPOSE) build --no-cache --pull

clean: down
	@echo "$(YELLOW)Очистка...$(NC)"
	$(COMPOSE) rm -f

clean-all: down
	@echo "$(RED)Полная очистка - удаление контейнеров, образов и volumes...$(NC)"
	$(COMPOSE) down -v --rmi all
	docker system prune -f

ps:
	$(COMPOSE) ps

# Команды приложения
bash:
	$(PHP) bash

bash-mysql:
	$(DB) bash

# Composer
composer-install:
	@echo "$(GREEN)Установка PHP зависимостей...$(NC)"
	$(PHP) composer install --prefer-dist --optimize-autoloader

composer-update:
	@echo "$(YELLOW)Обновление PHP зависимостей...$(NC)"
	$(PHP) composer update

composer-dump:
	$(PHP) composer dump-autoload

# Yii команды
yii:
	$(PHP) php /var/www/html/yii $(filter-out $@,$(MAKECMDGOALS))

# База данных
migrate:
	@echo "$(GREEN)Выполнение миграций базы данных...$(NC)"
	$(PHP) php /var/www/html/yii migrate --interactive=0

migrate-create:
	$(PHP) php /var/www/html/yii migrate/create $(filter-out $@,$(MAKECMDGOALS))

migrate-down:
	$(PHP) php /var/www/html/yii migrate/down

db-shell:
	$(DB) mysql -u user -ppassword storyvalut

# Инициализация проекта
init: up composer-install migrate
	@echo "$(GREEN)Инициализация проекта завершена!$(NC)"
	@echo "$(YELLOW)Приложение доступно по адресу: http://localhost:8080$(NC)"

# Права доступа (если нужно)
fix-permissions:
	@echo "$(YELLOW)Исправление прав доступа к файлам...$(NC)"
	$(PHP) chown -R www-data:www-data /var/www/html/web/assets
	$(PHP) chmod -R 755 /var/www/html/web/assets
	$(PHP) chmod -R 755 /var/www/html/runtime

# Тесты
test:
	@echo "$(GREEN)Запуск тестов...$(NC)"
	$(PHP) php /var/www/html/vendor/bin/codecept run

# Проверка состояния
health:
	@echo "$(YELLOW)Проверка состояния сервисов...$(NC)"
	@echo "PHP-FPM: $$(curl -s -o /dev/null -w '%{http_code}' http://localhost:8080 || echo 'недоступен')"
	@echo "MySQL: $$($(COMPOSE) exec mysql mysql -u user -ppassword -e 'SELECT 1;' 2>/dev/null && echo 'работает' || echo 'недоступен')"

# Обработка дополнительных аргументов
%:
	@: