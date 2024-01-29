<?php

namespace Tests\Cache;


use WPWCore\Cache\Repository;
use WPWCore\Cache\WpObjectCacheStore;
use WPWCore\Events\Dispatcher;
use WPWhales\Container\Container;
use WPWhales\Support\Carbon;
use Mockery as m;

class CacheWPStoreTest extends \WP_UnitTestCase
{

    public function set_up()
    {
        parent::set_up();

        Carbon::setTestNow(Carbon::parse(self::getTestDate()));
    }

    public function tear_down()
    {
        m::close();

        Carbon::setTestNow(null);
        parent::tear_down();

    }


//
    public function test_get_single_cache_value()
    {
        $cache = new WpObjectCacheStore();
        $key = "something-unique-" . uniqid();
        $value = uniqid();
        $cache->put($key, $value, 3600);

        $this->assertEquals($value, $cache->get($key));
    }

    public function test_get_multiple_cache_values()
    {
        $cache = new WpObjectCacheStore();
        $key = "something-unique-" . uniqid();
        $value = uniqid();
        $cache->put($key, $value, 3600);
        $key2 = "something-unique-" . uniqid();
        $value2 = uniqid();
        $cache->put($key2, $value2, 3600);
        $key3 = "something-unique-" . uniqid();
        $value3 = uniqid();
        $cache->put($key3, $value3, 3600);

        $this->assertIsArray($cache->get([$key, $key2, $key3]));

        $caches = $cache->get([$key, $key2, $key3]);
        $this->assertEquals($value, $caches[$key]);
        $this->assertEquals($value2, $caches[$key2]);
        $this->assertEquals($value3, $caches[$key3]);
    }

    public function test_put_multiple_cache_values()
    {

        $cache = new WpObjectCacheStore();

        $cache->putMany([
            "key1" => "value1",
            "key2" => "value2",
            "key3" => "value3",
            "key4" => "value4"
        ], 3600);
        $caches = $cache->many(["key1", "key2", "key3", "key4"]);
        $this->assertEquals("value1", $caches["key1"]);
        $this->assertEquals("value2", $caches["key2"]);
        $this->assertEquals("value3", $caches["key3"]);
        $this->assertEquals("value4", $caches["key4"]);


    }

//
    public function test_cache_group_is_wpwcore()
    {
        $cache = new WpObjectCacheStore();
        $key = "something-unique-" . uniqid();
        $value = uniqid();
        $cache->put($key, $value, 3600);

        $this->assertEquals($value, $cache->get($key));

        $this->assertEquals($value, wp_cache_get($key, "wpwcore"));
    }

    public function test_cache_add_value()
    {
        $cache = new WpObjectCacheStore();
        $key = "something-unique-" . uniqid();
        $value = uniqid();
        $cache->add($key, $value, 3600);

        $new_value = uniqid();
        $cache->add($key, $new_value, 3600);

        $this->assertNotEquals($new_value, $cache->get($key));

        $this->assertEquals($value, $cache->get($key));
    }


    public function test_cache_increment_value()
    {
        $cache = new WpObjectCacheStore();
        $key = "something-unique-" . uniqid();
        $cache->add($key, 0, 3600);
        $value = 1;
        $cache->increment($key, $value);

        $this->assertEquals($value, $cache->get($key));
        $cache->increment($key, 4);
        $this->assertEquals(5, $cache->get($key));
    }

    public function test_cache_decrement_value()
    {
        $cache = new WpObjectCacheStore();
        $key = "something-unique-" . uniqid();

        $cache->add($key, 5, 3600);

        $cache->decrement($key, 2);

        $this->assertEquals(3, $cache->get($key));
        $cache->decrement($key, 1);
        $this->assertEquals(2, $cache->get($key));


    }

    public function test_cache_forever()
    {
        $cache = $this->createPartialMock(WpObjectCacheStore::class, ["put"]);

        $key = "something-unique-" . uniqid();
        $value = uniqid();


        $cache->expects($this->once())->method("put")->with($key, $value, 0);
        $cache->forever($key, $value);

    }

    public function test_cache_forget()
    {
        $cache = new WpObjectCacheStore();
        $key = "something-unique-" . uniqid();

        $cache->add($key, 5, 3600);

        $this->assertEquals(5, $cache->get($key));
        $cache->forget($key);
        $this->assertEquals(false, $cache->get($key));
    }


    public function test_cache_flush_deletes_the_complete_group_wpwcore_only()
    {
        $cache = new WpObjectCacheStore();
        $keys = [];

        for ($x = 0; $x < 5; $x++) {
            $key = "something-unique-" . uniqid();
            $keys[$key] = 10 + $x;
            $cache->add($key, 10 + $x, 3600);
            $this->assertEquals(10 + $x, $cache->get($key));
        }

        $cache->flush();
        foreach ($keys as $k => $v) {
            $this->assertNull($cache->get($k));
        }

        wp_cache_add("random_key", "random_value", "random_group", 3600);

        $cache->flush();
        $this->assertEquals("random_value", wp_cache_get("random_key", "random_group"));

    }

    public function test_cache_remember()
    {

        $repository = $this->getRepository();

        $key = "something-unique-" . uniqid();
        $callback = function () {
            return uniqid();
        };
        $value = $repository->remember($key, 3600, $callback);

        $this->assertEquals($value, $repository->remember($key, 3600, $callback));
        $this->assertEquals($value, $repository->get($key));


    }

    public function test_pull_cache()
    {
        $repository = $this->getRepository();

        $key = "something-unique-" . uniqid();
        $value = uniqid();
        $repository->put($key, $value);
        $this->assertEquals($value, $repository->get($key));
        $this->assertEquals($value, $repository->pull($key));
        $this->assertNull($repository->get($key));

    }

    public function test_has_cache()
    {
        $repository = $this->getRepository();

        $key = "something-unique-" . uniqid();
        $value = uniqid();

        $repository->put($key, $value);
        $this->assertTrue($repository->has($key));
        $repository->forget($key);
        $this->assertFalse($repository->has($key));
    }


    public function test_missing_cache()
    {
        $repository = $this->getRepository();

        $key = "something-unique-" . uniqid();
        $value = uniqid();
        $repository->put($key, $value);
        $this->assertFalse($repository->missing($key));
        $repository->forget($key);
        $this->assertTrue($repository->missing($key));
    }


    public function test_support_tags()
    {

        $repository = $this->getRepository();


        $this->assertTrue($repository->supportsTags());
    }

    public function test_cache_tagging_is_working_and_tags_are_getting_saved_in_core_group()
    {
        $repository = $this->getRepository();
        $repository = $repository->tags("some_tag");

        $this->assertTrue(in_array($repository->getPrefix() . "some_tag", wp_cache_get("tags", $repository->getCoreGroup())));
    }

    public function test_put_values_in_tag()
    {
        $repository = $this->getRepository();
        $repository = $repository->tags("some_tag");

        $key = "something-unique-" . uniqid();
        $value = uniqid();
        $repository->put($key, $value, 3600);

        $this->assertEquals($value, wp_cache_get($key, $repository->getPrefix() . "some_tag"));
    }

    public function test_cache_tagging_allow_only_single_tag_using_parameters()
    {
        $this->expectException(\ArgumentCountError::class);
        $this->expectExceptionMessage("Please provide only 1 tag");
        $repository = $this->getRepository();
        $repository = $repository->tags("some_tag", "1231223");

    }

    public function test_cache_tagging_allow_only_single_tag_using_array()
    {
        $this->expectException(\ArgumentCountError::class);
        $this->expectExceptionMessage("Please provide only 1 tag");
        $repository = $this->getRepository();
        $repository = $repository->tags(["some_tag", "1231223"]);

    }


    public function test_cache_put_and_get_in_tag()
    {
        $repository = $this->getRepository();
        $repository = $repository->tags("some_tag");

        $key = "something-unique-" . uniqid();
        $value = uniqid();
        $repository->put($key, $value, 3600);

        $this->assertEquals($value, wp_cache_get($key, $repository->getTags()->tagKey("some_tag")));
        $this->assertEquals($repository->get($key), wp_cache_get($key, $repository->getTags()->tagKey("some_tag")));
    }

    public function test_cache_forget_key_in_specific_tag_only()
    {
        $repository = $this->getRepository();
        $repository = $repository->tags("some_tag");
        $key = "something-unique-" . uniqid();
        $value = uniqid();
        $repository->put($key, $value, 3600);

        $this->assertEquals($value, wp_cache_get($key, $repository->getTags()->tagKey("some_tag")));
        $repository->forget($key);
        $repository2 = $this->getRepository();
        $repository2 = $repository2->tags("some_tag_2");
        $repository2->put($key, $value, 3600);

        $this->assertEquals($value, $repository2->get($key));
        $this->assertNull($repository->get($key));


    }

    public function test_cache_flush_specific_tag(){
        $repository = $this->getRepository();
        $repository = $repository->tags("some_tag");
        $key = "something-unique-" . uniqid();
        $value = uniqid();
        $repository->put($key, $value, 3600);

        $repository2 = $this->getRepository();
        $repository2 = $repository2->tags("some_tag_2");
        $repository2->put($key, $value, 3600);

        $repository->flush();

        $this->assertEquals($value, $repository2->get($key));
        $this->assertNull($repository->get($key));

    }

    public function test_cache_flush_specific_tag_removes_entry_from_tags_in_core_tag(){
        $repository = $this->getRepository();
        $repository = $repository->tags("some_tag");
        $key = "something-unique-" . uniqid();
        $value = uniqid();
        $repository->put($key, $value, 3600);

        $repository2 = $this->getRepository();
        $repository2 = $repository2->tags("some_tag_2");
        $repository2->put($key, $value, 3600);

        $repository->flush();

        $this->assertEquals($value, $repository2->get($key));
        $this->assertNull($repository->get($key));


        $this->assertFalse(in_array($repository->getGroup(),$this->getRepository()->get("tags")));

    }

    public function test_cache_flush_everything(){
        $repository = $this->getRepository();
        $repository = $repository->tags("some_tag");
        $key = "something-unique-" . uniqid();
        $value = uniqid();
        $repository->put($key, $value, 3600);

        $repository2 = $this->getRepository();
        $repository2 = $repository2->tags("some_tag_2");
        $repository2->put($key, $value, 3600);

        $this->getRepository()->flush();

        $this->assertNull($repository2->get($key));
        $this->assertNull($repository->get($key));
    }

    protected function getRepository()
    {
        $dispatcher = new Dispatcher(m::mock(Container::class));
        $repository = new Repository(new WpObjectCacheStore());

        $repository->setEventDispatcher($dispatcher);

        return $repository;
    }


    protected static function getTestDate()
    {
        return '2030-07-25 12:13:14 UTC';
    }
}
