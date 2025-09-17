SHELL := /usr/bin/bash

up:
	docker compose up -d --build

down:
	docker compose down -v

logs:
	docker compose logs -f --tail=100

qr:
	docker compose exec app php scripts/generate_qr.php

