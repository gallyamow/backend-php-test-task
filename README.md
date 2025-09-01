# Modern PHP servers' benchmark

A microservice that records and exposes per-country usage statistics will be run on several servers such as:

- `apache + mod php` on http://localhost:8086/v1/statistics
- `nginx + php-fpm` on http://localhost:8087/v1/statistics
- `spiral/roadrunner-http` on http://localhost:8088/v1/statistics
- `php/frankenphp` on http://localhost:8089/v1/statistics
- `fastapi/fastapi` on http://localhost:8090/v1/statistics
- `Tornado vs Flask + Gunicorn` 
- `golang gin gonic` on http://localhost:8092/v1/statistics
- `NodeJS express`

**Results:**

This is not final results there are lack of some tuning.

| Server         | WRITE    | READ      |
|----------------|----------|-----------|
| apache+mod_php | 2670.65  | 2744.76   |
| nginx+fpm      | 6694.33  | 5985.75   |
| roadrunner     | 15363.60 | 13876.69  |
| frankenphp     | 1962.47  | 2436.10   |
| fastapi        | 6277.25  | 1724.79   |
| gin-gonic      | 47063.31 | 45043.10  |

## USAGE

Build containers, install PHP dependencies, and start the stack:

```bash
make up
make unit-test
make load-test
```