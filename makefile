apacheUrl = http://localhost:8086/v1/statistics
fmpUrl = http://localhost:8087/v1/statistics
roadrunnerUrl = http://localhost:8088/v1/statistics
frankenphpUrl = http://localhost:8089/v1/statistics
fastapiUrl = http://localhost:8090/v1/statistics
ginUrl = http://localhost:8092/v1/statistics

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

load-test-post-last:
	@echo ">>> ============ WRITE last ============ <<<"
	$(wrkCommand) $(ginUrl)
	@echo ">>> ============ READ last ============ <<<"
	$(wrkCommand) -s ./tests/load/countries.lua $(ginUrl)

load-test-post: wrk-pull
	@echo ">>> ============ WRITE apache + mod_php ============ <<<"
	$(wrkCommand) -s ./tests/load/countries.lua $(apacheUrl)
	@echo ">>> ============ WRITE nginx + fpm-fpm ============ <<<"
	$(wrkCommand) -s ./tests/load/countries.lua $(fmpUrl)
	@echo ">>> ============ WRITE roadrunner ============ <<<"
	$(wrkCommand) -s ./tests/load/countries.lua $(roadrunnerUrl)
	@echo ">>> ============ WRITE frankenphp ============ <<<"
	$(wrkCommand) -s ./tests/load/countries.lua $(frankenphpUrl)
	@echo ">>> ============ WRITE fastapi ============ <<<"
	$(wrkCommand) -s ./tests/load/countries.lua $(fastapiUrl)
	@echo ">>> ============ WRITE gin ============ <<<"
	$(wrkCommand) -s ./tests/load/countries.lua $(ginUrl)

load-test-get: wrk-pull
	@echo ">>> ============ READ apache + mod_php ============ <<<"
	$(wrkCommand) $(apacheUrl)
	@echo ">>> ============ READ nginx + fpm-fpm ============ <<<"
	$(wrkCommand) $(fmpUrl)
	@echo ">>> ============ READ roadrunner ============ <<<"
	$(wrkCommand) $(roadrunnerUrl)
	@echo ">>> ============ READ frankenphp ============ <<<"
	$(wrkCommand) $(frankenphpUrl)
	@echo ">>> ============READ fastapi ============ <<<"
	$(wrkCommand) $(fastapiUrl)
	@echo ">>> ============READ gin ============ <<<"
	$(wrkCommand) $(ginUrl)