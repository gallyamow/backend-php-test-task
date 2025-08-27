<?php
declare(strict_types=1);

// Prevent worker script termination when a client connection is interrupted
ignore_user_abort(true);

require __DIR__ . '/../vendor/autoload.php';

use App\Exception\AppExceptionInterface;
use App\Internal\BaseJsonLogger;
use App\Internal\CountryCodeValidator;
use App\Internal\RedisCounter;
use League\Route\Router;
use Nyholm\Psr7;
use Nyholm\Psr7Server;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server;
use Psr\Log;
use Laminas\HttpHandlerRunner\Emitter;

const RESPONSE_HEADERS = ['Content-Type' => 'application/json'];

// configure counter
$redisHost = getenv('REDIS_HOST');
$redisPort = (int)getenv('REDIS_PORT');
$redisStorageKey = getenv('REDIS_STORAGE_KEY');

$counter = new RedisCounter(new Redis(), $redisHost, $redisPort, $redisStorageKey);

// configure logger
$logger = new BaseJsonLogger('php://stdout');

// configure validator
$validator = new CountryCodeValidator();

// configure router
$router = new Router();

$router->middleware(new class($logger) implements Server\MiddlewareInterface {
    private Log\LoggerInterface $logger;

    public function __construct(Log\LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function process(ServerRequestInterface $request, Server\RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (AppExceptionInterface|\Webmozart\Assert\InvalidArgumentException $e) {
            $error = ['code' => $e->getCode(), 'message' => $e->getMessage()];
            $this->logger->error('Error occurred', ['exception' => $e]);

            return new Psr7\Response(
                500,
                RESPONSE_HEADERS,
                json_encode(['error' => $error], JSON_THROW_ON_ERROR)
            );
        } catch (Throwable $tr) {
            $error = ['code' => $tr->getCode(), 'message' => 'Internal server error'];
            $this->logger->error('Unknown error occurred', ['exception' => $tr]);

            return new Psr7\Response(
                502,
                RESPONSE_HEADERS,
                json_encode(['error' => $error], JSON_THROW_ON_ERROR)
            );
        }
    }
});

$router->map('GET', '/', function (ServerRequestInterface $request, array $args) use ($counter): ResponseInterface {
    return new Psr7\Response(
        200,
        RESPONSE_HEADERS,
        json_encode(['health' => 'good', 'server' => 'roadrunner'], JSON_THROW_ON_ERROR)
    );
});

$router->map('GET', '/v1/statistics', function (ServerRequestInterface $request, array $args) use ($counter): ResponseInterface {
    $body = $counter->getAllCounts();

    return new Psr7\Response(
        200,
        RESPONSE_HEADERS,
        json_encode($body, JSON_THROW_ON_ERROR)
    );
});

$router->map('POST', '/v1/statistics', function (ServerRequestInterface $request) use ($counter, $validator): ResponseInterface {
    $payload = json_decode($request->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
    $countryCode = strtolower($payload['countryCode'] ?? '');

    $validator->assertCountryCodeValid($countryCode);

    $counter->commitVisit($countryCode);

    return new Psr7\Response(
        201,
        RESPONSE_HEADERS,
        null
    );
});


// configure roadrunner
$psr17Factory = new Psr7\Factory\Psr17Factory();
$psr7Creator = new Psr7Server\ServerRequestCreator(
    $psr17Factory,
    $psr17Factory,
    $psr17Factory,
    $psr17Factory
);
$sapiEmitter = new Emitter\SapiEmitter();

// Handler outside the loop for better performance (doing less work)
$handler = static function () use ($psr7Creator, $sapiEmitter, $router) {
    $req = $psr7Creator->fromGlobals();
    $resp = $router->dispatch($req);
    $sapiEmitter->emit($resp);
};

$maxRequests = (int)($_SERVER['MAX_REQUESTS'] ?? 0);
for ($nbRequests = 0; !$maxRequests || $nbRequests < $maxRequests; ++$nbRequests) {
    $keepRunning = \frankenphp_handle_request($handler);

    // Call the garbage collector to reduce the chances of it being triggered in the middle of a page generation
    gc_collect_cycles();

    if (!$keepRunning) {
        break;
    }
}

