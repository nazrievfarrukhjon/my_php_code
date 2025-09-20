up:
	docker-compose up -d

composer_update:
	docker-compose run --rm composer_my_php_code composer update
composer_install:
	docker-compose run --rm composer_my_php_code composer install
down:
	docker-compose down
args ?=
cli:
	docker-compose run --rm php1 php ./entrypoint/cli.php $(args)

### make cli args="arg1 arg2"

# make cli file executable chmod +x ./entrypoint/cli
