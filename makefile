apacheUrl = http://localhost:8086/v1/statistics
fmpUrl = http://localhost:8087/v1/statistics
roadrunnerUrl = http://localhost:8088/v1/statistics
frankenphpUrl = http://localhost:8089/v1/statistics
fastapiUrl = http://localhost:8090/v1/statistics

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
	@echo ">>> ============ apache + mod_php ============ <<<"
	$(wrkCommand) -s ./tests/load/countries.lua $(apacheUrl)
	@echo ">>> ============ nginx + fpm-fpm ============ <<<"
	$(wrkCommand) -s ./tests/load/countries.lua $(fmpUrl)
	@echo ">>> ============ roadrunner ============ <<<"
	$(wrkCommand) -s ./tests/load/countries.lua $(roadrunnerUrl)
	@echo ">>> ============ frankenphp ============ <<<"
	$(wrkCommand) -s ./tests/load/countries.lua $(frankenphpUrl)
	@echo ">>> ============ fastapi ============ <<<"
	$(wrkCommand) -s ./tests/load/countries.lua $(fastapiUrl)

load-test-get: wrk-pull
	@echo ">>> ============ apache + mod_php ============ <<<"
	$(wrkCommand) $(apacheUrl)
	@echo ">>> ============ nginx + fpm-fpm ============ <<<"
	$(wrkCommand) $(fmpUrl)
	@echo ">>> ============ roadrunner ============ <<<"
	$(wrkCommand) $(roadrunnerUrl)
	@echo ">>> ============ frankenphp ============ <<<"
	$(wrkCommand) $(frankenphpUrl)
	@echo ">>> ============ fastapi ============ <<<"
	$(wrkCommand) $(fastapiUrl)