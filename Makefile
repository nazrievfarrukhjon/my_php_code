up:
	docker-compose up -d
cp_env:
	docker-compose run --rm php_my_php_code cp ./.env.example ./.env
composer_update:
	docker-compose run --rm composer_my_php_code composer update
composer_install:
	docker-compose run --rm composer_my_php_code composer install
key_gen:
	docker-compose run --rm artisan_my_php_code key:generate

migrate:
	docker-compose run --rm artisan_my_php_code migrate
seed:
	docker-compose run --rm artisan_my_php_code db:seed
queue:
	docker-compose run --rm artisan_my_php_code queue:work
npm_install:
	docker-compose run --rm node npm install
npm_run_build:
	docker-compose run --rm node npm run build
down:
	docker-compose down
optimize_clear:
	docker-compose run --rm artisan_my_php_code optimize:clear
#
RABBITMQ_CONTAINER_NAME = rabbitmq_my_php_code

create_rabbitmq_user:
# 	docker exec -it RABBITMQ_CONTAINER_NAME rabbitmqctl add_user user_name password
	docker exec -it $(RABBITMQ_CONTAINER_NAME) rabbitmqctl add_user my_php_code_test my_php_code_test
	docker exec -it $(RABBITMQ_CONTAINER_NAME) rabbitmqctl set_user_tags my_php_code_test administrator
	docker exec -it $(RABBITMQ_CONTAINER_NAME) rabbitmqctl set_permissions -p / my_php_code_test ".*" ".*" ".*"

rabbit_create_vhost:
	docker exec -it $(RABBITMQ_CONTAINER_NAME) rabbitmqctl add_vhost "my_php_code/test"

rabbit_create_permission:
	docker exec -it $(RABBITMQ_CONTAINER_NAME) rabbitmqctl set_permissions -p "my_php_code/test" my_php_code_test ".*" ".*" ".*"

