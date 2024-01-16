<?php
//
//namespace Tests\Console\Scheduling;
//
//use WPWCore\Cache\ArrayStore;
//use WPWCore\Console\Scheduling\CacheEventMutex;
//use WPWCore\Console\Scheduling\Event;
//use WPWhales\Contracts\Cache\Factory;
//use WPWhales\Contracts\Cache\Repository;
//use Mockery as m;
//use PHPUnit\Framework\TestCase;
//
//class CacheEventMutexTest extends TestCase
//{
//    /**
//     * @var \WPWCore\Console\Scheduling\CacheEventMutex
//     */
//    protected $cacheMutex;
//
//    /**
//     * @var \WPWCore\Console\Scheduling\Event
//     */
//    protected $event;
//
//    /**
//     * @var \WPWhales\Contracts\Cache\Factory
//     */
//    protected $cacheFactory;
//
//    /**
//     * @var \WPWhales\Contracts\Cache\Repository
//     */
//    protected $cacheRepository;
//
//    protected function setUp(): void
//    {
//        parent::setUp();
//
//        $this->cacheFactory = m::mock(Factory::class);
//        $this->cacheRepository = m::mock(Repository::class);
//        $this->cacheFactory->shouldReceive('store')->andReturn($this->cacheRepository);
//        $this->cacheMutex = new CacheEventMutex($this->cacheFactory);
//        $this->event = new Event($this->cacheMutex, 'command');
//    }
//
//    public function testPreventOverlap()
//    {
//        $this->cacheRepository->shouldReceive('getStore')->andReturn(new \stdClass);
//        $this->cacheRepository->shouldReceive('add')->once();
//
//        $this->cacheMutex->create($this->event);
//    }
//
//    public function testCustomConnection()
//    {
//        $this->cacheRepository->shouldReceive('getStore')->andReturn(new \stdClass);
//        $this->cacheFactory->shouldReceive('store')->with('test')->andReturn($this->cacheRepository);
//        $this->cacheRepository->shouldReceive('add')->once();
//        $this->cacheMutex->useStore('test');
//
//        $this->cacheMutex->create($this->event);
//    }
//
//    public function testPreventOverlapFails()
//    {
//        $this->cacheRepository->shouldReceive('getStore')->andReturn(new \stdClass);
//        $this->cacheRepository->shouldReceive('add')->once()->andReturn(false);
//
//        $this->assertFalse($this->cacheMutex->create($this->event));
//    }
//
//    public function testOverlapsForNonRunningTask()
//    {
//        $this->cacheRepository->shouldReceive('getStore')->andReturn(new \stdClass);
//        $this->cacheRepository->shouldReceive('has')->once()->andReturn(false);
//
//        $this->assertFalse($this->cacheMutex->exists($this->event));
//    }
//
//    public function testOverlapsForRunningTask()
//    {
//        $this->cacheRepository->shouldReceive('getStore')->andReturn(new \stdClass);
//        $this->cacheRepository->shouldReceive('has')->once()->andReturn(true);
//
//        $this->assertTrue($this->cacheMutex->exists($this->event));
//    }
//
//    public function testResetOverlap()
//    {
//        $this->cacheRepository->shouldReceive('getStore')->andReturn(new \stdClass);
//        $this->cacheRepository->shouldReceive('forget')->once();
//
//        $this->cacheMutex->forget($this->event);
//    }
//
//    public function testPreventOverlapWithLockProvider()
//    {
//        $this->cacheRepository->shouldReceive('getStore')->andReturn(new ArrayStore);
//
//        $this->assertTrue($this->cacheMutex->create($this->event));
//    }
//
//    public function testPreventOverlapFailsWithLockProvider()
//    {
//        $this->cacheRepository->shouldReceive('getStore')->andReturn(new ArrayStore);
//
//        // first create the lock, so we can test that the next call fails.
//        $this->cacheMutex->create($this->event);
//
//        $this->assertFalse($this->cacheMutex->create($this->event));
//    }
//
//    public function testOverlapsForNonRunningTaskWithLockProvider()
//    {
//        $this->cacheRepository->shouldReceive('getStore')->andReturn(new ArrayStore);
//
//        $this->assertFalse($this->cacheMutex->exists($this->event));
//    }
//
//    public function testOverlapsForRunningTaskWithLockProvider()
//    {
//        $this->cacheRepository->shouldReceive('getStore')->andReturn(new ArrayStore);
//
//        $this->cacheMutex->create($this->event);
//
//        $this->assertTrue($this->cacheMutex->exists($this->event));
//    }
//
//    public function testResetOverlapWithLockProvider()
//    {
//        $this->cacheRepository->shouldReceive('getStore')->andReturn(new ArrayStore);
//
//        $this->cacheMutex->create($this->event);
//
//        $this->cacheMutex->forget($this->event);
//
//        $this->assertFalse($this->cacheMutex->exists($this->event));
//    }
//}
