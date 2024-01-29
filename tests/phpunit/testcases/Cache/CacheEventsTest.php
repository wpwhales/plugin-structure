<?php
//
//namespace Tests\Cache;
//
//use WPWCore\Cache\ArrayStore;
//use WPWCore\Cache\Events\CacheHit;
//use WPWCore\Cache\Events\CacheMissed;
//use WPWCore\Cache\Events\KeyForgotten;
//use WPWCore\Cache\Events\KeyWritten;
//use WPWCore\Cache\Repository;
//use WPWhales\Contracts\Cache\Store;
//use WPWCore\Events\Dispatcher;
//use Mockery as m;
//use PHPUnit\Framework\TestCase;
//
//class CacheEventsTest extends TestCase
//{
//    protected function tearDown(): void
//    {
//        m::close();
//    }
//
//    public function testHasTriggersEvents()
//    {
//        $dispatcher = $this->getDispatcher();
//        $repository = $this->getRepository($dispatcher);
//
//        $dispatcher->shouldReceive('dispatch')->once()->with($this->assertEventMatches(CacheMissed::class, ['key' => 'foo']));
//        $this->assertFalse($repository->has('foo'));
//
//        $dispatcher->shouldReceive('dispatch')->once()->with($this->assertEventMatches(CacheHit::class, ['key' => 'baz', 'value' => 'qux']));
//        $this->assertTrue($repository->has('baz'));
//
//
//    }
//
//    public function testGetTriggersEvents()
//    {
//        $dispatcher = $this->getDispatcher();
//        $repository = $this->getRepository($dispatcher);
//
//        $dispatcher->shouldReceive('dispatch')->once()->with($this->assertEventMatches(CacheMissed::class, ['key' => 'foo']));
//        $this->assertNull($repository->get('foo'));
//
//        $dispatcher->shouldReceive('dispatch')->once()->with($this->assertEventMatches(CacheHit::class, ['key' => 'baz', 'value' => 'qux']));
//        $this->assertSame('qux', $repository->get('baz'));
//
//
//    }
//
//    public function testPullTriggersEvents()
//    {
//        $dispatcher = $this->getDispatcher();
//        $repository = $this->getRepository($dispatcher);
//
//        $dispatcher->shouldReceive('dispatch')->once()->with($this->assertEventMatches(CacheHit::class, ['key' => 'baz', 'value' => 'qux']));
//        $dispatcher->shouldReceive('dispatch')->once()->with($this->assertEventMatches(KeyForgotten::class, ['key' => 'baz']));
//        $this->assertSame('qux', $repository->pull('baz'));
//    }
//
//
//
//    public function testPutTriggersEvents()
//    {
//        $dispatcher = $this->getDispatcher();
//        $repository = $this->getRepository($dispatcher);
//
//        $dispatcher->shouldReceive('dispatch')->once()->with($this->assertEventMatches(KeyWritten::class, ['key' => 'foo', 'value' => 'bar', 'seconds' => 99]));
//        $repository->put('foo', 'bar', 99);
//
//
//    }
//
//    public function testAddTriggersEvents()
//    {
//        $dispatcher = $this->getDispatcher();
//        $repository = $this->getRepository($dispatcher);
//
//        $dispatcher->shouldReceive('dispatch')->once()->with($this->assertEventMatches(CacheMissed::class, ['key' => 'foo']));
//        $dispatcher->shouldReceive('dispatch')->once()->with($this->assertEventMatches(KeyWritten::class, ['key' => 'foo', 'value' => 'bar', 'seconds' => 99]));
//        $this->assertTrue($repository->add('foo', 'bar', 99));
//
//
//    }
//
//    public function testForeverTriggersEvents()
//    {
//        $dispatcher = $this->getDispatcher();
//        $repository = $this->getRepository($dispatcher);
//
//        $dispatcher->shouldReceive('dispatch')->once()->with($this->assertEventMatches(KeyWritten::class, ['key' => 'foo', 'value' => 'bar', 'seconds' => null]));
//        $repository->forever('foo', 'bar');
//
//    }
//
//    public function testRememberTriggersEvents()
//    {
//        $dispatcher = $this->getDispatcher();
//        $repository = $this->getRepository($dispatcher);
//
//        $dispatcher->shouldReceive('dispatch')->once()->with($this->assertEventMatches(CacheMissed::class, ['key' => 'foo']));
//        $dispatcher->shouldReceive('dispatch')->once()->with($this->assertEventMatches(KeyWritten::class, ['key' => 'foo', 'value' => 'bar', 'seconds' => 99]));
//        $this->assertSame('bar', $repository->remember('foo', 99, function () {
//            return 'bar';
//        }));
//
//
//    }
//
//    public function testRememberForeverTriggersEvents()
//    {
//        $dispatcher = $this->getDispatcher();
//        $repository = $this->getRepository($dispatcher);
//
//        $dispatcher->shouldReceive('dispatch')->once()->with($this->assertEventMatches(CacheMissed::class, ['key' => 'foo']));
//        $dispatcher->shouldReceive('dispatch')->once()->with($this->assertEventMatches(KeyWritten::class, ['key' => 'foo', 'value' => 'bar', 'seconds' => null]));
//        $this->assertSame('bar', $repository->rememberForever('foo', function () {
//            return 'bar';
//        }));
//
//
//    }
//
//    public function testForgetTriggersEvents()
//    {
//        $dispatcher = $this->getDispatcher();
//        $repository = $this->getRepository($dispatcher);
//
//        $dispatcher->shouldReceive('dispatch')->once()->with($this->assertEventMatches(KeyForgotten::class, ['key' => 'baz']));
//        $this->assertTrue($repository->forget('baz'));
//
//
//    }
//
//    public function testForgetDoesNotTriggerEventOnFailure()
//    {
//        $dispatcher = $this->getDispatcher();
//        $store = m::mock(Store::class);
//        $store->shouldReceive('forget')->andReturn(false);
//        $repository = new Repository($store);
//        $repository->setEventDispatcher($dispatcher);
//
//        $dispatcher->shouldReceive('dispatch')->never();
//        $this->assertFalse($repository->forget('baz'));
//    }
//
//    protected function assertEventMatches($eventClass, $properties = [])
//    {
//        return m::on(function ($event) use ($eventClass, $properties) {
//            if (! $event instanceof $eventClass) {
//                return false;
//            }
//
//            foreach ($properties as $name => $value) {
//                if ($value != $event->$name) {
//                    return false;
//                }
//            }
//
//            return true;
//        });
//    }
//
//    protected function getDispatcher()
//    {
//        return m::mock(Dispatcher::class);
//    }
//
//    protected function getRepository($dispatcher)
//    {
//        $repository = new Repository(new ArrayStore);
//        $repository->put('baz', 'qux', 99);
//        $repository->setEventDispatcher($dispatcher);
//
//        return $repository;
//    }
//}
