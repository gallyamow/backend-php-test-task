import Fastify from 'fastify'
import Redis from 'ioredis'

const serverType = process.env.SERVER_TYPE;
const redisHost = process.env.REDIS_HOST;
const redisPort = parseInt(process.env.REDIS_PORT, 10);
const redisStorageKey = process.env.REDIS_STORAGE_KEY;

const redisClient = new Redis({host: redisHost, port: redisPort});

const fastifyIndex = Fastify({
    logger: true
})

fastifyIndex.get('/', async function handler(request, reply) {
    return {'health': 'good', 'server': serverType}
})

fastifyIndex.get('/v1/statistics', async function handler(request, reply) {
    const stats = await redisClient.hgetall(redisStorageKey);

    const result = {};
    for (const [key, val] of Object.entries(stats)) {
        result[key] = parseInt(val, 10)
    }

    return result
})

fastifyIndex.post('/v1/statistics', async function handler(request, reply) {
    const {countryCode} = request.body;
    await redisClient.hincrby(redisStorageKey, countryCode, 1);

    reply.code(201).send();
})

try {
    await fastifyIndex.listen({host: '0.0.0.0', port: 8080})
} catch (err) {
    fastifyIndex.log.error(err)
    process.exit(1);
}