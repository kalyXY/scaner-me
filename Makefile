# QR Attendance System - Development Makefile
SHELL := /usr/bin/bash

.PHONY: help up down restart logs shell mysql install test cs-check cs-fix analyse qr health clean

help: ## Show this help message
	@echo "QR Attendance System - Available commands:"
	@echo ""
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-20s\033[0m %s\n", $$1, $$2}'

# Docker commands
up: ## Start the application stack
	docker compose up -d --build
	@echo "✅ Application started at http://localhost:8080"

down: ## Stop the application stack
	docker compose down -v

restart: ## Restart the application stack
	docker compose restart

logs: ## Show application logs
	docker compose logs -f --tail=100

logs-db: ## Show database logs
	docker compose logs -f db

shell: ## Access application container shell
	docker compose exec app bash

mysql: ## Access MySQL shell
	docker compose exec db mysql -u root -p school_mvp

# Development commands
install: ## Install PHP dependencies
	docker compose exec app composer install

install-dev: ## Install PHP dependencies including dev tools
	docker compose exec app composer install --dev

update: ## Update PHP dependencies
	docker compose exec app composer update

# Testing and Quality
test: ## Run unit tests
	docker compose exec app composer test

test-coverage: ## Run tests with coverage
	docker compose exec app vendor/bin/phpunit --coverage-html coverage/

cs-check: ## Check code style
	docker compose exec app composer cs-check

cs-fix: ## Fix code style issues
	docker compose exec app composer cs-fix

analyse: ## Run static analysis
	docker compose exec app composer analyse

quality: cs-check analyse test ## Run all quality checks

# Application commands
qr: ## Generate QR codes for all active students
	docker compose exec app php scripts/generate_qr.php

health: ## Check application health
	@echo "🔍 Checking application health..."
	@curl -s http://localhost:8080/api/health | jq '.' || echo "❌ Health check failed"

db-schema: ## Import database schema
	docker compose exec db mysql -u root -p school_mvp < schema.sql

db-backup: ## Backup database
	docker compose exec db mysqldump -u root -p school_mvp > backup_$(shell date +%Y%m%d_%H%M%S).sql

# Maintenance
clean: ## Clean up containers, images and volumes
	docker compose down -v
	docker system prune -f

clean-logs: ## Clean application logs
	docker compose exec app truncate -s 0 logs/app.log

# Production
build: ## Build production Docker image
	docker build -t qr-attendance:latest .

deploy-prod: build ## Deploy to production (customize as needed)
	@echo "🚀 Production deployment - customize this target for your environment"

# Quick development setup
setup: up install db-schema qr ## Complete setup for new development environment
	@echo ""
	@echo "🎉 Setup complete!"
	@echo "📊 Dashboard: http://localhost:8080"
	@echo "🔍 Health: http://localhost:8080/api/health"
	@echo "📝 Logs: make logs"

# Status check
status: ## Show application status
	@echo "📊 QR Attendance System Status"
	@echo "================================"
	@docker compose ps
	@echo ""
	@echo "🔍 Health Check:"
	@curl -s http://localhost:8080/api/health | jq '.status // "unavailable"' || echo "❌ Application not responding"

