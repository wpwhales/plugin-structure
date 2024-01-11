<?php

namespace Tests\View;



use WPWCore\Http\Request;
use WPWCore\View\ViewException;

/**
 *  !!!! ONLY FOR AJAX CALL !!!!
 *
 * Tests for the Ajax calls to save and get sos stats.
 * For speed, non ajax calls of class-ajax.php are tested in test-ajax-others.php
 * Ajax tests are not marked risky when run in separate processes and wp_debug
 * disabled. But, this makes tests slow so non ajax calls are kept separate
 *
 *
 */
class ViewCompileTest extends \WP_UnitTestCase
{



    public function set_up()
    {
        parent::set_up();
        $paths = \WPWCore\config("view.paths");
        $paths[] = __DIR__;

        $config = \WPWCore\app("config");
        $config->set("view.paths", $paths);





    }


    public function test_compiled_file_gets_created(){

        /**
         * @var $view \WPWCore\View\Factory
         */
        $sessionInstance = $this->getMockBuilder(Request::class)->onlyMethods(["session","get"])->getMock();

        $sessionInstance->expects($this->exactly(1))->method("session")->will($this->returnSelf());

        $sessionInstance->expects($this->exactly(1))->method("get")->will($this->returnValue(null));
        $this->app["request"] = $sessionInstance;

        $view = $this->app["view"];




        $view->make("test")->render();

        $blade  = $this->app["view"]->make("test");
        $basename = basename($blade->getEngine()->getCompiler()->getCompiledPath($blade->getPath()));

        $this->assertTrue(file_exists(\WPWCore\config("view.compiled")."/".$basename));


    }


    public function test_compiled_files_contains_ABSPATH_constant_check(){


        $sessionInstance = $this->getMockBuilder(Request::class)->onlyMethods(["session","get"])->getMock();

        $sessionInstance->expects($this->exactly(1))->method("session")->will($this->returnSelf());

        $sessionInstance->expects($this->exactly(1))->method("get")->will($this->returnValue(null));
        $this->app["request"] = $sessionInstance;

        /**
         * @var $view \WPWCore\View\Factory
         */
        $view = $this->app["view"];



        $view->make("test")->render();

        $blade  = $this->app["view"]->make("test");
        $basename = basename($blade->getEngine()->getCompiler()->getCompiledPath($blade->getPath()));

        $this->assertTrue(file_exists(\WPWCore\config("view.compiled")."/".$basename));

        $content = file_get_contents(\WPWCore\config("view.compiled")."/".$basename);

        $this->assertStringContainsString("<?php /*DIE ON FAIL*/ if(!defined('ABSPATH')) exit; ?>",$content);


    }



    public function test_exception_thrown_in_view(){


        $sessionInstance = $this->getMockBuilder(Request::class)->onlyMethods(["session","get"])->getMock();

        $sessionInstance->expects($this->exactly(1))->method("session")->will($this->returnSelf());

        $sessionInstance->expects($this->exactly(1))->method("get")->will($this->returnValue(null));
        $this->app["request"] = $sessionInstance;

        /**
         * @var $view \WPWCore\View\Factory
         */
        $view = $this->app["view"];



        $this->expectException(ViewException::class);
        $result = $view->make("error")->render();



    }

}

