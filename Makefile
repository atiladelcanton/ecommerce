make.PHONY: setup up down shell test migrate fresh build pint

setup:
	./scripts/setup.sh

up:
	./vendor/bin/sail up -d

down:
	./vendor/bin/sail down

shell:
	./vendor/bin/sail shell

test:
	./vendor/bin/sail test

migrate:
	./vendor/bin/sail artisan migrate

fresh:
	./vendor/bin/sail artisan migrate:fresh --seed

build:
	./vendor/bin/sail npm install
	./vendor/bin/sail npm run build

pint:
	./vendor/bin/sail pint
