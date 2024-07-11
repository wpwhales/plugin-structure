<?php

namespace Tests\DashboardNotices;


use Mockery\Mock;
use WPWCore\Assets\Asset\Asset;
use WPWCore\Assets\Manifest;
use WPWCore\DashboardNotices\AdminNotice;
use WPWCore\Http\Request;
use WPWCore\Options\OptionsField;
use WPWCore\Options\OptionsPage;
use WPWCore\Options\OptionsSection;
use WPWCore\Testing\DatabaseMigrations;
use Mockery as m;
use function WPWCore\app;
use function WPWCore\base_path;

class NoticeHandlerTest extends \WP_Ajax_UnitTestCase
{


    public function testNoticeShouldBeEmpty()
    {

        AdminNotice::info("Hello World");

        ob_start();
        do_action("admin_notices");
        $content = ob_get_clean();

        $this->assertEmpty($content);
    }

    public function testNoticeShouldNotBeEmpty()
    {

        AdminNotice::init();
        AdminNotice::info("Hello World");

        ob_start();
        do_action("admin_notices");
        $content = ob_get_clean();

        $this->assertStringContainsString("Hello World", $content);
    }


    public function testNoticeDismissNoNonce()
    {

        AdminNotice::init();


        $this->expectException(\WPAjaxDieStopException::class);
        $this->expectExceptionMessage(-1);
        $this->_handleAjax(AdminNotice::DISMISS_ACTION);


    }

    public function testNoticeDismissValidNonce()
    {
        AdminNotice::init();
        $this->expectException(\WPAjaxDieStopException::class);
        $this->expectExceptionMessage("Access denied. You need to be logged in to dismiss notices.");
        $_POST["_wpnonce"] = wp_create_nonce(AdminNotice::DISMISS_ACTION);

        $this->_handleAjax(AdminNotice::DISMISS_ACTION);


    }

    public function testNoticeDismissActiveValid()
    {

        AdminNotice::init();
        $user_id = $this->factory()->user->create();
        wp_set_current_user($user_id);
        $_POST["_wpnonce"] = wp_create_nonce(AdminNotice::DISMISS_ACTION);
        $_POST["notice_id"] = "123";
        try{
            $this->_handleAjax(AdminNotice::DISMISS_ACTION);
        }catch(\WPAjaxDieContinueException $e){

        }

        $arr = json_decode($this->_last_response,true);
       $this->assertIsArray($arr);
       $this->assertTrue($arr["success"]);


    }


}



