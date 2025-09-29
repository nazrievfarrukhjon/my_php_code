up:
	docker-compose up -d

composer_update:
	docker-compose run --rm php1 composer update
composer_install:
	docker-compose run --rm php1 composer install
down:
	docker-compose down

# to run migration run this command: make cli args=migrate

args ?=
cli:
	docker-compose run --rm php1 php ./entrypoint/cli.php $(args)
