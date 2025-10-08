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

# Elasticsearch seeders
es-create-rides-index:
	docker-compose run --rm php1 php database/seeders/elasticsearch/CreateRidesIndexSeeder.php

es-bulk-index-rides:
	docker-compose run --rm php1 php database/seeders/elasticsearch/BulkIndexRidesSeeder.php

es-seed-all:
	docker-compose run --rm php1 php database/seeders/elasticsearch/ElasticsearchSeeder.php

es-geo-demo:
	docker-compose run --rm php1 php database/seeders/elasticsearch/GeoDistanceSearchDemo.php
