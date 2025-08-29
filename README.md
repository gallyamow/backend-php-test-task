# Modern PHP servers' benchmark

A microservice that records and exposes per-country usage statistics will be run on several servers such as:

- `apache + mod php` on http://localhost:8086/
- `nginx + php-fpm` on http://localhost:8087/
- `spiral/roadrunner-http` on http://localhost:8088/
- `php/frankenphp` on http://localhost:8089/
- `python fast-api + Uvicorn` vs Tornado
- `golang gin gonic`

**Roadrunner results:**

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

## USAGE

Build containers, install PHP dependencies, and start the stack:

```bash
make up
make unit-test
make load-test
```

