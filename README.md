# Modern WEB servers' benchmark

A microservice that records and exposes per-country usage statistics will be run on several servers such as:

- `php: apache + mod php` on http://localhost:8086/v1/statistics
- `php: nginx + php-fpm` on http://localhost:8087/v1/statistics
- `php: roadrunner-http` on http://localhost:8088/v1/statistics
- `php: frankenphp` on http://localhost:8089/v1/statistics
- `python: fastapi/fastapi` on http://localhost:8090/v1/statistics
- `python: Tornado vs Flask + Gunicorn` 
- `golang: gin gonic` on http://localhost:8092/v1/statistics
- `nodejs: fastify` on http://localhost:8093/v1/statistics

**Results:*8093

This is not final results there are lack of some tuning.

| Server              | WRITE    | READ      |
|---------------------|----------|-----------|
| php: apache+mod_php | 2670.65  | 2744.76   |
| php: nginx+fpm      | 6694.33  | 5985.75   |
| php: roadrunner     | 15363.60 | 13876.69  |
| php: frankenphp     | 1962.47  | 2436.10   |
| python: fastapi     | 247.10   | 7634.92   |
| golang: gin-gonic   | 47063.31 | 45043.10  |
| nodejs: fastify     | 29821.35 | 20990.24  |

## USAGE

Build containers, install PHP dependencies, and start the stack:

```bash
make up
make unit-test
make load-test-all
# or
make load-test port=...
```