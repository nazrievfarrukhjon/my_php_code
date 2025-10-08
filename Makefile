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

# Swagger/API testing
swagger-generate:
	docker-compose run --rm php1 php test_swagger.php

swagger-view:
	@echo "Open http://localhost:8002/docs in your browser to view Swagger UI"
	@echo "Or view the static HTML at: http://localhost:8002/swagger.html"

api-test:
	@echo "Testing API endpoints..."
	@echo "1. Create rides index:"
	@curl -X POST http://localhost:8002/api/elasticsearch/indices/rides
	@echo -e "\n\n2. Get index stats:"
	@curl -X GET http://localhost:8002/api/elasticsearch/indices/rides/stats
	@echo -e "\n\n3. Search rides:"
	@curl -X GET "http://localhost:8002/api/elasticsearch/rides/search?size=5"
