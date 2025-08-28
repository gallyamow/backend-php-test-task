<?php

declare(strict_types=1);

// Prevent worker script termination when a client connection is interrupted
ignore_user_abort(true);

require __DIR__ . '/../vendor/autoload.php';

use App\Internal\BaseJsonLogger;
use App\Internal\CountryCodeValidator;
use App\Internal\RedisCounter;
use App\Internal\RequestHandler;
use Laminas\HttpHandlerRunner\Emitter;
use Nyholm\Psr7;
use Nyholm\Psr7Server;

$redisHost = getenv('REDIS_HOST');
$redisPort = (int)getenv('REDIS_PORT');
$redisStorageKey = getenv('REDIS_STORAGE_KEY');

// configure request handler
$handler = new RequestHandler(
    'index',
    new RedisCounter(new Redis(), $redisHost, $redisPort, $redisStorageKey),
    new BaseJsonLogger('php://stderr'),
    new CountryCodeValidator()
);

$psr17Factory = new Psr7\Factory\Psr17Factory();
$psr7Creator = new Psr7Server\ServerRequestCreator(
    $psr17Factory,
    $psr17Factory,
    $psr17Factory,
    $psr17Factory
);
$sapiEmitter = new Emitter\SapiEmitter();

$sapiEmitter->emit($handler->handleRequest($psr7Creator->fromGlobals()));