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
    'frankenphp-worker',
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

// Handler outside the loop for better performance (doing less work)
$handler = static function () use ($psr7Creator, $sapiEmitter, $handler) {
    $sapiEmitter->emit($handler->handleRequest($psr7Creator->fromGlobals()));
};

// configure frankenphp
$maxRequests = (int)($_SERVER['MAX_REQUESTS'] ?? 0);
for ($nbRequests = 0; !$maxRequests || $nbRequests < $maxRequests; ++$nbRequests) {
    $keepRunning = \frankenphp_handle_request($handler);

    // Call the garbage collector to reduce the chances of it being triggered in the middle of a page generation
    gc_collect_cycles();

    if (!$keepRunning) {
        break;
    }
}

