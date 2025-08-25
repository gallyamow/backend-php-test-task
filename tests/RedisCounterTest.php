<?php

declare(strict_types=1);

namespace Tests;

use App\Exception\StorageException;
use App\RedisCounter;
use PHPUnit\Framework\TestCase;
use Redis;
use RedisException;

class RedisCounterTest extends TestCase
{
    private Redis $redisMock;
    private RedisCounter $counter;
    private string $testKey = 'test_counter';

    protected function setUp(): void
    {
        $this->redisMock = $this->createMock(Redis::class);
        $this->counter = new RedisCounter($this->redisMock, 'localhost', 6379, $this->testKey);
    }

    public function testCommitVisitIncrementsCounter(): void
    {
        $countryCode = 'US';

        $this->redisMock->expects($this->once())
            ->method('isConnected')
            ->willReturn(true);

        $this->redisMock->expects($this->once())
            ->method('hIncrBy')
            ->with($this->testKey, $countryCode, 1)
            ->willReturn(1);

        $this->counter->commitVisit($countryCode);
    }

    public function testCommitVisitReconnectsWhenDisconnected(): void
    {
        $countryCode = 'DE';

        $this->redisMock->expects($this->once())
            ->method('isConnected')
            ->willReturn(false);

        $this->redisMock->expects($this->once())
            ->method('connect')
            ->with('localhost', 6379);

        $this->redisMock->expects($this->once())
            ->method('hIncrBy')
            ->with($this->testKey, $countryCode, 1);

        $this->counter->commitVisit($countryCode);
    }

    public function testCommitVisitThrowsExceptionOnRedisError(): void
    {
        $countryCode = 'fr';

        $this->redisMock->expects($this->once())
            ->method('isConnected')
            ->willReturn(true);

        $this->redisMock->expects($this->once())
            ->method('hIncrBy')
            ->willThrowException(new RedisException('Connection failed'));

        $this->expectException(StorageException::class);
        $this->expectExceptionCode(StorageException::CODE_FAILED_TO_COMMIT);

        $this->counter->commitVisit($countryCode);
    }

    public function testGetAllCountsReturnsData(): void
    {
        $expectedData = ['US' => '5', 'DE' => '3'];

        $this->redisMock->expects($this->once())
            ->method('isConnected')
            ->willReturn(true);

        $this->redisMock->expects($this->once())
            ->method('hGetAll')
            ->with($this->testKey)
            ->willReturn($expectedData);

        $result = $this->counter->getAllCounts();

        $this->assertEquals($expectedData, $result);
    }

    public function testGetAllCountsThrowsExceptionOnFailure(): void
    {
        $this->redisMock->expects($this->once())
            ->method('isConnected')
            ->willReturn(true);

        $this->redisMock->expects($this->once())
            ->method('hGetAll')
            ->willReturn(false);

        $this->expectException(StorageException::class);
        $this->expectExceptionCode(StorageException::CODE_FAILED_TO_FOUND);

        $this->counter->getAllCounts();
    }
}