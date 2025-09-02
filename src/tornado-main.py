import asyncio
import json
import os
import tornado
from redis import asyncio as aioredis
from redis.asyncio.connection import ConnectionPool

SERVER_TYPE = str(os.getenv("SERVER_TYPE"))
REDIS_HOST = str(os.getenv("REDIS_HOST"))
REDIS_PORT = int(os.getenv("REDIS_PORT"))
REDIS_STORAGE_KEY = str(os.getenv("REDIS_STORAGE_KEY"))

redis_pool = ConnectionPool.from_url("redis://%s:%d" % (REDIS_HOST, REDIS_PORT), max_connections=100)
async_redis = aioredis.Redis(connection_pool=redis_pool)


class BaseHandler(tornado.web.RequestHandler):
    def set_default_headers(self):
        self.set_header("Content-Type", "application/json")

    def write_error(self, status_code, **kwargs):
        self.finish({
            "error": {
                "code": status_code,
                "message": self._reason
            }
        })


class IndexHandler(BaseHandler):
    async def get(self):
        self.write({'health': 'good', 'server': SERVER_TYPE})


class StatisticsHandler(BaseHandler):
    async def get(self):
        stats = await async_redis.hgetall(REDIS_STORAGE_KEY)

        # result = {key.decode('utf-8'): int(value) for key, value in stats.items()}

        result = {}
        for key, value in stats.items():
            if len(result) % 100 == 0:
                # optimization
                await asyncio.sleep(0)
            result[key.decode('utf-8')] = int(value)

        self.write(result)

    async def post(self):
        data = json.loads(self.request.body)
        country_code = data.get("countryCode")

        await async_redis.hincrby(REDIS_STORAGE_KEY, country_code, 1)
        self.set_status(201)
        await self.finish()


def make_app():
    return tornado.web.Application([
        ("/", IndexHandler),
        ("/v1/statistics", StatisticsHandler),
    ])


async def main():
    app = make_app()
    app.listen(8080)

    await asyncio.Event().wait()


if __name__ == "__main__":
    asyncio.run(main())
