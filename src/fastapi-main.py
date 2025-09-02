import asyncio
import os
from typing import Union
from fastapi import FastAPI, Response, status
from pydantic import BaseModel
from redis import asyncio as aioredis
from redis.asyncio.connection import ConnectionPool

# @see https://fastapi.tiangolo.com/ru/#_4
app = FastAPI()

SERVER_TYPE = str(os.getenv("SERVER_TYPE"))
REDIS_HOST = str(os.getenv("REDIS_HOST"))
REDIS_PORT = int(os.getenv("REDIS_PORT"))
REDIS_STORAGE_KEY = str(os.getenv("REDIS_STORAGE_KEY"))

redis_pool = ConnectionPool.from_url("redis://%s:%d" % (REDIS_HOST, REDIS_PORT), max_connections=100)
async_redis = aioredis.Redis(connection_pool=redis_pool)


class Visit(BaseModel):
    countryCode: str


@app.get("/")
async def index():
    return {'health': 'good', 'server': SERVER_TYPE}


@app.get("/v1/statistics")
async def read_statistics(q: Union[str, None] = None):
    stats = await async_redis.hgetall(REDIS_STORAGE_KEY)

    # move cpu bound ops to separate thread
    # read - 386.97 rps
    # return await asyncio.to_thread(
    #     lambda: {key: int(value) for key, value in stats.items()}
    # )

    # read - 714.38
    # result = {}
    # for key, value in stats.items():
    #     await asyncio.sleep(0)
    #     result[key] = int(value)
    #
    # return result

    return {key: int(value) for key, value in stats.items()}

@app.post("/v1/statistics")
async def write_statistics(visit: Visit):
    await async_redis.hincrby(REDIS_STORAGE_KEY, visit.countryCode, 1)
    return Response(status_code=status.HTTP_201_CREATED)
