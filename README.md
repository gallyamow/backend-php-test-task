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

Давно хотел познакомиться с проектом `roadrunner-server/roadrunner` - поэтому выберу его.
Для роутинга буду использовать `league/route`, для redis - `phpredis`.

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
