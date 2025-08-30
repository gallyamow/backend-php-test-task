# Modern PHP servers' benchmark

A microservice that records and exposes per-country usage statistics will be run on several servers such as:

- `apache + mod php` on http://localhost:8086/
- `nginx + php-fpm` on http://localhost:8087/
- `spiral/roadrunner-http` on http://localhost:8088/
- `php/frankenphp` on http://localhost:8089/
- `fastapi/fastapi` on on http://localhost:8090/ 
- `Tornado vs Flask + Gunicorn` 
- `golang gin gonic`
- `NodeJS express`

**Results:**

This is not final results there are lack of some tuning.
# Сравнение производительности PHP-серверов

Результаты нагрузочного тестирования различных конфигураций PHP-серверов.

## Результаты RPS (Requests Per Second)

| Server          | WRITE    | READ     |
|-----------------|----------|----------|
| apache+mod_php  | 434.05   | 418.19   |
| nginx+fpm       | 904.01   | 935.48   |
| roadrunner      | 16979.37 | 14121.75 |
| frankenphp      | 540.87   | 538.36   |


```
docker pull elswork/wrk
Using default tag: latest
latest: Pulling from elswork/wrk
Digest: sha256:529f8fe35924e549cc270d4128406d5863043e756c31395afd08acccc74ada79
Status: Image is up to date for elswork/wrk:latest
docker.io/elswork/wrk:latest
>>> ============ apache + mod_php ============ <<<
docker run --rm --network="host" --volume /Users/ramil/Projects/backend-php-test-task:/wrk -w /wrk elswork/wrk -t12 -c20 -d5s -s ./tests/load/countries.lua http://localhost:8086/v1/statistics
Running 5s test @ http://localhost:8086/v1/statistics
  12 threads and 20 connections
  Thread Stats   Avg      Stdev     Max   +/- Stdev
    Latency    45.90ms  147.33ms   1.85s    97.76%
    Req/Sec    37.96     14.17   101.00     74.70%
  2213 requests in 5.10s, 367.67KB read
Requests/sec:    434.05
Transfer/sec:     72.11KB
>>> ============ nginx + fpm-fpm ============ <<<
docker run --rm --network="host" --volume /Users/ramil/Projects/backend-php-test-task:/wrk -w /wrk elswork/wrk -t12 -c20 -d5s -s ./tests/load/countries.lua http://localhost:8087/v1/statistics
Running 5s test @ http://localhost:8087/v1/statistics
  12 threads and 20 connections
  Thread Stats   Avg      Stdev     Max   +/- Stdev
    Latency    13.10ms    5.62ms  57.32ms   73.51%
    Req/Sec    76.35     25.64   290.00     79.30%
  4610 requests in 5.10s, 0.87MB read
Requests/sec:    904.01
Transfer/sec:    174.80KB
>>> ============ roadrunner ============ <<<
docker run --rm --network="host" --volume /Users/ramil/Projects/backend-php-test-task:/wrk -w /wrk elswork/wrk -t12 -c20 -d5s -s ./tests/load/countries.lua http://localhost:8088/v1/statistics
Running 5s test @ http://localhost:8088/v1/statistics
  12 threads and 20 connections
  Thread Stats   Avg      Stdev     Max   +/- Stdev
    Latency   744.82us  651.28us  10.89ms   89.47%
    Req/Sec     1.43k   122.87     1.83k    68.20%
  86549 requests in 5.10s, 9.24MB read
Requests/sec:  16979.37
Transfer/sec:      1.81MB
>>> ============ frankenphp ============ <<<
docker run --rm --network="host" --volume /Users/ramil/Projects/backend-php-test-task:/wrk -w /wrk elswork/wrk -t12 -c20 -d5s -s ./tests/load/countries.lua http://localhost:8089/v1/statistics
Running 5s test @ http://localhost:8089/v1/statistics
  12 threads and 20 connections
  Thread Stats   Avg      Stdev     Max   +/- Stdev
    Latency    22.25ms   15.81ms 121.32ms   71.69%
    Req/Sec    45.36     15.68   101.00     81.25%
  2759 requests in 5.10s, 412.23KB read
Requests/sec:    540.87
Transfer/sec:     80.81KB
>>> ============ apache + mod_php ============ <<<
docker run --rm --network="host" --volume /Users/ramil/Projects/backend-php-test-task:/wrk -w /wrk elswork/wrk -t12 -c20 -d5s http://localhost:8086/v1/statistics
Running 5s test @ http://localhost:8086/v1/statistics
  12 threads and 20 connections
  Thread Stats   Avg      Stdev     Max   +/- Stdev
    Latency    35.34ms   76.91ms   1.21s    98.74%
    Req/Sec    35.57     14.40    80.00     53.92%
  2095 requests in 5.01s, 3.11MB read
Requests/sec:    418.19
Transfer/sec:    634.69KB
>>> ============ nginx + fpm-fpm ============ <<<
docker run --rm --network="host" --volume /Users/ramil/Projects/backend-php-test-task:/wrk -w /wrk elswork/wrk -t12 -c20 -d5s http://localhost:8087/v1/statistics
Running 5s test @ http://localhost:8087/v1/statistics
  12 threads and 20 connections
  Thread Stats   Avg      Stdev     Max   +/- Stdev
    Latency    12.80ms    5.25ms  49.20ms   71.55%
    Req/Sec    78.21     18.02   130.00     60.17%
  4691 requests in 5.01s, 7.10MB read
Requests/sec:    935.48
Transfer/sec:      1.41MB
>>> ============ roadrunner ============ <<<
docker run --rm --network="host" --volume /Users/ramil/Projects/backend-php-test-task:/wrk -w /wrk elswork/wrk -t12 -c20 -d5s http://localhost:8088/v1/statistics
Running 5s test @ http://localhost:8088/v1/statistics
  12 threads and 20 connections
  Thread Stats   Avg      Stdev     Max   +/- Stdev
    Latency     1.43ms    6.21ms 150.34ms   98.46%
    Req/Sec     1.20k   351.05     1.66k    74.09%
  72014 requests in 5.10s, 103.98MB read
Requests/sec:  14121.75
Transfer/sec:     20.39MB
>>> ============ frankenphp ============ <<<
docker run --rm --network="host" --volume /Users/ramil/Projects/backend-php-test-task:/wrk -w /wrk elswork/wrk -t12 -c20 -d5s http://localhost:8089/v1/statistics
Running 5s test @ http://localhost:8089/v1/statistics
  12 threads and 20 connections
  Thread Stats   Avg      Stdev     Max   +/- Stdev
    Latency    22.42ms   15.94ms 110.85ms   70.93%
    Req/Sec    44.95     16.16   111.00     80.33%
  2698 requests in 5.01s, 3.95MB read
Requests/sec:    538.36
Transfer/sec:    808.06KB

```

## USAGE

Build containers, install PHP dependencies, and start the stack:

```bash
make up
make unit-test
make load-test
```

