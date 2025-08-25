<?php

declare(strict_types=1);

namespace App;

use \Psr\Log;

final class BaseJsonLogger extends Log\AbstractLogger
{
    private mixed $outputStream;

    public function __construct(mixed $outputStream = null)
    {
        // @see https://docs.roadrunner.dev/docs/error-codes/stdout-crc
        $this->outputStream = STDERR;
    }

    public function log($level, \Stringable|string $message, array $context = []): void
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $logEntry = [
            'timestamp' => (new \DateTimeImmutable())->format(\DATE_RFC3339_EXTENDED),
            'level' => $level,
            'message' => $this->interpolate($message, $context),
            'context' => $context
        ];

        /** @noinspection PhpUnhandledExceptionInspection */
        fwrite($this->outputStream, json_encode($logEntry, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE) . PHP_EOL);
    }

    /**
     * @throws \JsonException
     */
    private function interpolate($message, array $context = array()): string
    {
        $replace = [];
        foreach ($context as $key => $val) {
            if ($val instanceof \Throwable) {
                // $val = json_encode(['code' => $val->getCode(), 'message' => $val->getMessage(),
                // 'file' => $val->getFile(),
                // 'line' => $val->getLine()], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
                $val = json_encode((string)$val, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
            } elseif (is_array($val) || is_object($val)) {
                $val = json_encode($val, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
            } elseif (is_bool($val)) {
                $val = $val ? 'true' : 'false';
            } elseif (is_null($val)) {
                $val = 'null';
            }

            $replace['{' . $key . '}'] = $val;
        }

        return strtr($message, $replace);
    }
}