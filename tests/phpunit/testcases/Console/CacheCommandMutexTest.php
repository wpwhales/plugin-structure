<?php

namespace Tests\Console;

use WPWCore\Console\CacheCommandMutex;
use WPWCore\Console\Command;
use WPWhales\Contracts\Cache\Factory;
use WPWhales\Contracts\Cache\Repository;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class CacheCommandMutexTest extends TestCase
{
    /**
     * @var \WPWCore\Console\CacheCommandMutex
     */
    protected $mutex;

    /**
     * @var \WPWCore\Console\Command
     */
    protected $command;

    /**
     * @var \WPWhales\Contracts\Cache\Factory
     */
    protected $cacheFactory;

    /**
     * @var \WPWhales\Contracts\Cache\Repository
     */
    protected $cacheRepository;

    protected function setUp(): void
    {
        $this->cacheFactory = m::mock(Factory::class);
        $this->cacheRepository = m::mock(Repository::class);
        $this->cacheFactory->shouldReceive('store')->andReturn($this->cacheRepository);
        $this->mutex = new CacheCommandMutex($this->cacheFactory);
        $this->command = new class extends Command
        {
            protected $name = 'command-name';
        };
    }

    public function testCanCreateMutex()
    {
        $this->cacheRepository->shouldReceive('add')
            ->andReturn(true)
            ->once();
        $actual = $this->mutex->create($this->command);

        $this->assertTrue($actual);
    }

    public function testCannotCreateMutexIfAlreadyExist()
    {
        $this->cacheRepository->shouldReceive('add')
            ->andReturn(false)
            ->once();
        $actual = $this->mutex->create($this->command);

        $this->assertFalse($actual);
    }

    public function testCanCreateMutexWithCustomConnection()
    {
        $this->cacheRepository->shouldReceive('getStore')
            ->with('test')
            ->andReturn($this->cacheRepository);
        $this->cacheRepository->shouldReceive('add')
            ->andReturn(false)
            ->once();
        $this->mutex->useStore('test');

        $this->mutex->create($this->command);
    }
}
