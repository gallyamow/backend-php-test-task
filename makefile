baseUrl = http://localhost:8088/v1/statistics
wrkImage = elswork/wrk
wrkCommand = docker run --rm --network="host" --volume $(CURDIR):/wrk -w /wrk $(wrkImage) -t12 -c20 -d5s
composerCommand = docker compose run --rm app composer

up: composer-install
	docker compose up -d --build

down:
	docker compose stop

ps:
	docker compose ps

logs:
	docker compose logs -f

composer-install:
	$(composerCommand) install

composer-update:
	$(composerCommand) update

unit-test:
	docker compose run --rm app ./vendor/bin/phpunit --testdox tests

load-test: load-test-post load-test-get

wrk-pull:
	docker pull $(wrkImage)

load-test-post: wrk-pull
	$(wrkCommand) -s ./tests/load/countries.lua $(baseUrl)

load-test-get: wrk-pull
	$(wrkCommand) $(baseUrl)