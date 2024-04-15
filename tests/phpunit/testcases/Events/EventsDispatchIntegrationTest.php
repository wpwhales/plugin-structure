<?php

namespace Tests\Events;


use WPWCore\Events\CallQueuedListener;
use WPWhales\Contracts\Queue\ShouldQueue;
use WPWhales\Support\Facades\Event;
use WPWhales\Support\Facades\Log;
use WPWhales\Support\Facades\Queue;

class EventsDispatchIntegrationTest extends \WP_UnitTestCase
{

    static $listening = false;

    public function set_up()
    {
        parent::set_up();

        $this->app["events"]->listen(ExampleEvent2::class,TestEventListenerIntegration::class);
    }

    public function test_event_dispatch_and_listener_is_binded(){


        Event::fake();

        Event::dispatch(new ExampleEvent2());


        Event::assertDispatched(ExampleEvent2::class);

        Event::assertListening(ExampleEvent2::class,TestEventListenerIntegration::class);
        Event::assertDispatchedTimes(ExampleEvent2::class,1);

    }


    public function test_event_triggers_the_handle_method_of_listner(){

        $this->assertFalse(self::$listening);
        Event::dispatch(ExampleEvent2::class);
        $this->assertTrue(self::$listening);


    }


    public function test_event_trigger_queues_a_listner()
    {
        $this->app["events"]->listen(ExampleEvent2::class,TestEventListenerIntegrationQueable::class);

        Queue::fake();
        Queue::assertNothingPushed();
        Event::dispatch(ExampleEvent2::class);
        Queue::assertPushed(CallQueuedListener::class,function($job){

            return $job->class === TestEventListenerIntegrationQueable::class;
        });

    }
}


class ExampleEvent2
{
    //
}

class TestEventListenerIntegration
{
    public function handle()
    {
        EventsDispatchIntegrationTest::$listening = true;
    }


}

class TestEventListenerIntegrationQueable implements ShouldQueue
{
    public function handle()
    {
        return 12;
    }


}
