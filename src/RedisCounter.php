<?php

declare(strict_types=1);

namespace App;

use App\Exception\StorageException;
use Webmozart\Assert\Assert;

class RedisCounter implements CounterInterface
{
    private \Redis $redisClient;
    private string $redisHost;
    private int $redisPort;
    private string $storageKey;

    public function __construct(\Redis $redisClient, string $redisHost, int $redisPort, string $storageKey)
    {
        Assert::stringNotEmpty($redisHost, sprintf('Invalid "%s"', 'redisHost'));
        Assert::positiveInteger($redisPort, sprintf('Invalid "%s"', 'redisPort'));
        Assert::stringNotEmpty($storageKey, sprintf('Invalid "%s"', 'redisKey'));

        $this->redisClient = $redisClient;
        $this->redisHost = $redisHost;
        $this->redisPort = $redisPort;
        $this->storageKey = $storageKey;
    }

    public function commitVisit(string $countryCode): void
    {
        try {
            if (!$this->redisClient->isConnected()) {
                $this->redisClient->connect($this->redisHost, $this->redisPort);
            }

            $this->redisClient->hIncrBy($this->storageKey, $countryCode, 1);
        } catch (\RedisException $e) {
            throw StorageException::failedToCommit($countryCode, $e);
        }
    }

    public function getAllCounts(): array
    {
        try {
            if (!$this->redisClient->isConnected()) {
                $this->redisClient->connect($this->redisHost, $this->redisPort);
            }

            $res = $this->redisClient->hGetAll($this->storageKey);
        } catch (\RedisException $e) {
            throw StorageException::failedToRead($this->storageKey);
        }

        if (false === $res) {
            throw StorageException::failedToFound($this->storageKey);
        }

        return array_map('intval', $res);
    }
}