apacheUrl = http://localhost:8086/v1/statistics
fmpUrl = http://localhost:8087/v1/statistics
roadrunnerUrl = http://localhost:8088/v1/statistics
frankenphpUrl = http://localhost:8089/v1/statistics
fastapiUrl = http://localhost:8090/v1/statistics
ginUrl = http://localhost:8092/v1/statistics
fastifyUrl = http://localhost:8093/v1/statistics

wrkImage = elswork/wrk
wrkCommand = docker run --rm --network="host" --volume ${CURDIR}:/wrk -w /wrk $(wrkImage) -t12 -c20 -d5s --latency
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

wrk-pull:
	docker pull $(wrkImage)

load-test-all: wrk-pull
	make load-test port=8086
	make load-test port=8087
	make load-test port=8088
	make load-test port=8089
	make load-test port=8090
	make load-test port=8092
	make load-test port=8093

load-test:
	@echo ">>> ============ WRITE $(port) ============ <<<"
	$(wrkCommand) http://localhost:$(port)/v1/statistics
	@echo ">>> ============ READ $(port) ============ <<<"
	$(wrkCommand) -s ./tests/load/countries.lua http://localhost:$(port)/v1/statistics
