# Modern WEB servers' benchmark

A microservice that records and exposes per-country usage statistics will be run on several servers such as:

- `php: apache + mod php` on http://localhost:8086/v1/statistics
- `php: nginx + php-fpm` on http://localhost:8087/v1/statistics
- `php: roadrunner-http` on http://localhost:8088/v1/statistics
- `php: frankenphp` on http://localhost:8089/v1/statistics
- `python: fastapi/fastapi` on http://localhost:8090/v1/statistics
- `python: tornado` on http://localhost:8091/v1/statistics 
- `golang: gin gonic` on http://localhost:8092/v1/statistics
- `nodejs: fastify` on http://localhost:8093/v1/statistics

**Results:**

This is not final results there are lack of some tuning.

| Server              | WRITE    | READ     |
|---------------------|----------|----------|
| php: apache+mod_php | 2508.89  | 2904.01  |
| php: nginx+fpm      | 6020.13  | 6305.22  |
| php: roadrunner     | 15524.43 | 16649.91 |
| php: frankenphp     | 2278.72  | 2664.04  |
| python: fastapi     | 14476.52 | 762.28   |
| python: tornado     | 4167.74  | 1936.03  |
| golang: gin-gonic   | 44931.60 | 23433.03 |
| nodejs: fastify     | 17986.04 | 8790.46  |

## USAGE

Build containers, install PHP dependencies, and start the stack:

```bash
make up
make unit-test
make load-test-all
# or
make load-test port=...
```