<?php

require __DIR__ . '/../vendor/autoload.php';

use App\BaseJsonLogger;
use App\CountryCodeValidator;
use App\Exception\AppExceptionInterface;
use App\RedisCounter;
use League\Route\Router;
use Nyholm\Psr7;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server;
use Psr\Log;
use Spiral\RoadRunner;

const CONTENT_TYPE_JSON = 'application/json';

// configure counter
$redisHost = getenv('REDIS_HOST');
$redisPort = (int)getenv('REDIS_PORT');
$redisStorageKey = getenv('REDIS_STORAGE_KEY');

$counter = new RedisCounter(new Redis(), $redisHost, $redisPort, $redisStorageKey);

// configure logger
$logger = new BaseJsonLogger();

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
                ['Content-Type' => CONTENT_TYPE_JSON],
                json_encode(['error' => $error], JSON_THROW_ON_ERROR)
            );
        } catch (Throwable $tr) {
            $error = ['code' => $tr->getCode(), 'message' => 'Internal server error'];
            $this->logger->error('Unknown error occurred', ['exception' => $tr]);

            return new Psr7\Response(
                502,
                ['Content-Type' => CONTENT_TYPE_JSON],
                json_encode(['error' => $error], JSON_THROW_ON_ERROR)
            );
        }
    }
});

$router->map('GET', '/', function (ServerRequestInterface $request, array $args) use ($counter): ResponseInterface {
    return new Psr7\Response(
        200,
        ['Content-Type' => CONTENT_TYPE_JSON],
        json_encode(['health' => 'good'], JSON_THROW_ON_ERROR)
    );
});

$router->map('GET', '/v1/statistics', function (ServerRequestInterface $request, array $args) use ($counter): ResponseInterface {
    $body = $counter->getAllCounts();

    return new Psr7\Response(
        200,
        ['Content-Type' => CONTENT_TYPE_JSON],
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
        ['Content-Type' => CONTENT_TYPE_JSON],
        null
    );
});

// configure roadrunner
$rrFactory = new Psr7\Factory\Psr17Factory();
$rrWorker = new RoadRunner\Http\PSR7Worker(RoadRunner\Worker::create(), $rrFactory, $rrFactory, $rrFactory);

while ($req = $rrWorker->waitRequest()) {
    try {
        $resp = $router->dispatch($req);
        $rrWorker->respond($resp);
    } catch (\Throwable $e) {
        $rrWorker->getWorker()->error((string)$e);
    }
}