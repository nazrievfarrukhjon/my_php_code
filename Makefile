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
	docker-compose run --rm php_my_php_code php start.php $(args)

### make cli args="arg1 arg2"