<?php

declare(strict_types=1);

namespace App\Internal;

use App\Exception\AppExceptionInterface;
use League\Route\Router;
use Nyholm\Psr7;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server;
use Psr\Log;
use Webmozart\Assert\InvalidArgumentException;

const RESPONSE_HEADERS = ['Content-Type' => 'application/json'];

class RequestHandler
{
    private const URL_INDEX = "/";
    private const URL_STATS = "/v1/statistics";

    private string $serverType;
    private RedisCounter $counter;
    private Log\LoggerInterface $logger;
    private CountryCodeValidator $validator;
    private Router $router;

    public function __construct(string $serverType, CounterInterface $counter, Log\LoggerInterface $logger, CountryCodeValidator $validator)
    {
        $this->serverType = $serverType;
        $this->counter = $counter;
        $this->logger = $logger;
        $this->validator = $validator;

        $this->initRouter();
    }

    public function handleRequest(ServerRequestInterface $req): ResponseInterface
    {
        return $this->router->dispatch($req);
    }

    private function initRouter(): void
    {
        // configure router
        $this->router = new Router();

        $this->router->middleware(new class($this->logger) implements Server\MiddlewareInterface {
            private Log\LoggerInterface $aLogger;

            public function __construct(Log\LoggerInterface $logger)
            {
                $this->aLogger = $logger;
            }

            public function process(ServerRequestInterface $request, Server\RequestHandlerInterface $handler): ResponseInterface
            {
                try {
                    return $handler->handle($request);
                } catch (AppExceptionInterface|InvalidArgumentException $e) {
                    $error = ['code' => $e->getCode(), 'message' => $e->getMessage()];
                    $this->aLogger->error('Error occurred', ['exception' => $e]);

                    return new Psr7\Response(
                        500,
                        RESPONSE_HEADERS,
                        json_encode(['error' => $error], JSON_THROW_ON_ERROR)
                    );
                } catch (\Throwable $tr) {
                    $error = ['code' => $tr->getCode(), 'message' => 'Internal server error'];
                    $this->aLogger->error('Unknown error occurred', ['exception' => $tr]);

                    return new Psr7\Response(
                        502,
                        RESPONSE_HEADERS,
                        json_encode(['error' => $error], JSON_THROW_ON_ERROR)
                    );
                }
            }
        });

        $this->router->map('GET', self::URL_INDEX, function (ServerRequestInterface $request, array $args): ResponseInterface {
            return new Psr7\Response(
                200,
                RESPONSE_HEADERS,
                json_encode(['health' => 'good', 'server' => $this->serverType], JSON_THROW_ON_ERROR)
            );
        });

        $this->router->map('GET', self::URL_STATS, function (ServerRequestInterface $request, array $args): ResponseInterface {
            $body = $this->counter->getAllCounts();

            return new Psr7\Response(
                200,
                RESPONSE_HEADERS,
                json_encode($body, JSON_THROW_ON_ERROR)
            );
        });

        $this->router->map('POST', self::URL_STATS, function (ServerRequestInterface $request): ResponseInterface {
            $payload = json_decode($request->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
            $countryCode = strtolower($payload['countryCode'] ?? '');

            $this->validator->assertCountryCodeValid($countryCode);

            $this->counter->commitVisit($countryCode);

            return new Psr7\Response(
                201,
                RESPONSE_HEADERS,
                null
            );
        });
    }

    private function buildResponse(int $statusCode, ?array $body): ResponseInterface
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        return new Psr7\Response(
            $statusCode,
            RESPONSE_HEADERS,
            json_encode($body, JSON_THROW_ON_ERROR)
        );
    }
}