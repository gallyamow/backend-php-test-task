<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Internal\BaseJsonLogger;
use App\Internal\CountryCodeValidator;
use App\Internal\RedisCounter;
use App\Internal\RequestHandler;
use Nyholm\Psr7;
use Spiral\RoadRunner;

$redisHost = getenv('REDIS_HOST');
$redisPort = (int)getenv('REDIS_PORT');
$redisStorageKey = getenv('REDIS_STORAGE_KEY');

// configure request handler
$handler = new RequestHandler(
    'roadrunner-worker',
    new RedisCounter(new Redis(), $redisHost, $redisPort, $redisStorageKey),
    new BaseJsonLogger('php://stderr'),
    new CountryCodeValidator()
);

// configure roadrunner
$psr17Factory = new Psr7\Factory\Psr17Factory();
$rrWorker = new RoadRunner\Http\PSR7Worker(RoadRunner\Worker::create(), $psr17Factory, $psr17Factory, $psr17Factory);

/** @noinspection PhpUnhandledExceptionInspection */
while ($req = $rrWorker->waitRequest()) {
    try {
        $resp = $handler->handleRequest($req);
        $rrWorker->respond($resp);
    } catch (\Throwable $e) {
        $rrWorker->getWorker()->error((string)$e);
    }
}