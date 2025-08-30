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

| Server          | WRITE    | READ     |
|-----------------|----------|----------|
| apache+mod_php  | 434.05   | 418.19   |
| nginx+fpm       | 5900.66     | 935.48   |
| roadrunner      | 16979.37 | 14121.75 |
| frankenphp      | 540.87   | 538.36   |

## USAGE

Build containers, install PHP dependencies, and start the stack:

```bash
make up
make unit-test
make load-test
```

