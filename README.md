# Statistics API
Template for a lightweight PHP micro-service to record and expose per-country usage statistics.

## Solution

Основная сложность этой задачи в том что нужно обеспечить атомарный инкремент показателей и быстрое чтение сразу всех
показателей. К счастью в Redis уже есть и такая операция - `HINCRBY` и такой тип `HASH`.

1. В Redis создаем значение с типом `HASH` и ключом `statistics_counter`.
2. При обновлении статистики - используем `HINCRBY`
3. При запросе статистики - используем `HGETALL`

Так как функциональность сервиса - небольшая (микросервис), то не буду использовать никаких фреймворков. Возьму
предоставленный шаблон.

Давно хотел познакомиться с решениями вида `roadrunner-server/roadrunner`, поэтому сделаю на его основе.
Для роутинга буду использовать `league/route`, для redis - `phpredis`.

**Результат load-tests**

```
### load-test-post
docker run --rm --network="host" --volume /Users/ramil/Projects/backend-php-test-task:/wrk -w /wrk elswork/wrk -t12 -c20 -d5s -s ./tests/load/countries.lua http://localhost:8088/v1/statistics
Running 5s test @ http://localhost:8088/v1/statistics
  12 threads and 20 connections
  Thread Stats   Avg      Stdev     Max   +/- Stdev
    Latency     2.75ms   12.88ms 160.13ms   97.21%
    Req/Sec     1.37k   342.38     1.82k    85.81%
  81716 requests in 5.10s, 8.73MB read
Requests/sec:  16023.91
Transfer/sec:      1.71MB

### load-test-get
docker run --rm --network="host" --volume /Users/ramil/Projects/backend-php-test-task:/wrk -w /wrk elswork/wrk -t12 -c20 -d5s http://localhost:8088/v1/statistics
Running 5s test @ http://localhost:8088/v1/statistics
  12 threads and 20 connections
  Thread Stats   Avg      Stdev     Max   +/- Stdev
    Latency   776.65us  773.45us  16.78ms   90.44%
    Req/Sec     1.41k   270.33     2.84k    78.90%
  84513 requests in 5.10s, 137.10MB read
Requests/sec:  16573.27
Transfer/sec:     26.89MB
```

## Getting Started
Build containers, install PHP dependencies, and start the stack:

```bash
make up
```

## Endpoints

### Update Statistics

```bash
curl -X POST http://127.0.0.1:8088/v1/statistics \
     -H "Content-Type: application/json" \
     -d '{"countryCode": "ru"}'
```
Response:
```
201 Created
```

### Get Country Statistics

```bash
curl -X GET http://127.0.0.1:8088/v1/statistics \
     -H "Content-Type: application/json"
```
Response:
```json
{
  "ru": 813,
  "us": 456,
  "it": 92,
  "de": 17,
  "cy": 123
}
```

## Run Unit Tests
Execute the full PHPUnit suite:

```bash
make unit-test
```


## Run Load Tests

```bash
make load-test
```
