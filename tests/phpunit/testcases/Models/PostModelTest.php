<?php

namespace Tests\Hooks;

use WPWCore\Database\Eloquent\Model;
use WPWhales\Support\Facades\DB;

class PostModelTest extends \WP_UnitTestCase
{


    public function test_post_value()
    {
        $post_id = $this->factory()->post->create();
        //TODO We'll test it later with transaction . currently we have two different connections with both $wpdb and eloquent

        $this->assertInstanceOf(Model::class,Post::find($post_id));
    }

}


class Post extends Model
{

    protected $table = "posts";

    protected $primaryKey = "ID";

    public $timestamps = false;
}
