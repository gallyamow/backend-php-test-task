roadrunnerUrl = http://localhost:8088/v1/statistics
frankenphpUrl = http://localhost:8089/v1/statistics

wrkImage = elswork/wrk
wrkCommand = docker run --rm --network="host" --volume ${CURDIR}:/wrk -w /wrk $(wrkImage) -t12 -c20 -d5s
composerCommand = docker run --rm --volume ${CURDIR}:/app --interactive composer:latest composer

up: composer-install
	docker compose up -d --build

down:
	docker compose down

ps:
	docker compose ps

logs:
	docker compose logs -f

composer-install:
	$(composerCommand) install --optimize-autoloader --ignore-platform-reqs

composer-update:
	$(composerCommand) update --optimize-autoloader --ignore-platform-reqs

unit-test:
	docker compose run --rm app ./vendor/bin/phpunit --testdox tests

load-test: load-test-post load-test-get

wrk-pull:
	docker pull $(wrkImage)

load-test-post: wrk-pull
	$(wrkCommand) -s ./tests/load/countries.lua $(roadrunnerUrl)
	$(wrkCommand) -s ./tests/load/countries.lua $(frankenphpUrl)

load-test-get: wrk-pull
	$(wrkCommand) $(roadrunnerUrl)
	$(wrkCommand) $(frankenphpUrl)