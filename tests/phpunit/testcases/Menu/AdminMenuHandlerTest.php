<?php

namespace Tests\Routing;

use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use WPWCore\Exceptions\WPWException;
use WPWCore\Exceptions\WPWExceptionInterface;
use WPWCore\Menu\MenuBuilder;
use WPWCore\Routing\Controller;
use WPWhales\Http\Request;
use WPWhales\Support\Facades\Menu;
use WPWhales\Testing\TestResponse;


/**
 *  !!!! ONLY FOR WEB CALL !!!!
 *
 * Tests for the Ajax calls to save and get sos stats.
 * For speed, non ajax calls of class-ajax.php are tested in test-ajax-others.php
 * Ajax tests are not marked risky when run in separate processes and wp_debug
 * disabled. But, this makes tests slow so non ajax calls are kept separate
 *
 *
 */
class AdminMenuHandlerTest extends \WP_UnitTestCase
{

    public function set_up()
    {
        parent::set_up();
    }

    public function testCheckMenuRegister()
    {


    }

    public function testIsMenuRegistered(){
        global $wp_filter;
        $app = $this->app;


        $this->assertIsInt(has_action("admin_menu",[$app,"loadAdminMenus"]));
    }

    /**
     * Test creating a single menu.
     *
     * @return void
     */
    public function testCreateSingleMenu()
    {

        $handler = function () {
            // Menu handler
        };
        $capability = 'manage_options';
        $pageTitle = 'Test Menu';

        $menu = Menu::add($pageTitle, $handler, $capability)->routeName("xyz");


        $this->assertInstanceOf(\WPWCore\Menu\Menu::class, $menu);
        $this->assertEquals($pageTitle, $menu->getPageTitle());
        $this->assertEquals($pageTitle, $menu->getName());
        $this->assertEquals($capability, $menu->getCapability());
        $this->assertEquals('test-menu', $menu->getSlug());
        $this->assertEquals($handler, $menu->getHandler());

        $this->assertEquals("http://localhost/wp-admin/admin.php?page=test-menu", Menu::getUrl("xyz"));

    }

    /**
     * Test creating a grouped menu.
     *
     * @return void
     */
    public function testCreateGroupedMenu()
    {

        $parentHandler = function () {
            // Parent menu handler
        };
        $childHandler = function () {
            // Child menu handler
        };
        $capability = 'manage_options';
        $parentTitle = 'Parent Menu';
        $childTitle = 'Child Menu';

        $parentMenu = Menu::group($parentTitle, $parentHandler, $capability, function ($builder) use ($childTitle,$capability, $childHandler) {
            $builder->add($childTitle, $childHandler);
            $builder->add($childTitle, $childHandler,$capability)->routeName("xyz");
        }, 'parent-route');

        //looks like it's on 1 index as per our code
        $childMenu = Menu::getMenus()[1];
        $childMenu2 = Menu::getMenus()[2];
        $this->assertInstanceOf(\WPWCore\Menu\Menu::class, $parentMenu);
        $this->assertEquals($parentTitle, $parentMenu->getPageTitle());
        $this->assertEquals($parentTitle, $parentMenu->getName());
        $this->assertEquals($capability, $parentMenu->getCapability());
        $this->assertEquals('parent-menu', $parentMenu->getSlug());
        $this->assertEquals($parentHandler, $parentMenu->getHandler());
        $this->assertEquals('parent-route', $parentMenu->getRouteName());

        $this->assertInstanceOf(\WPWCore\Menu\Menu::class, $childMenu);
        $this->assertEquals($childTitle, $childMenu->getPageTitle());
        $this->assertEquals($childTitle, $childMenu->getName());
        $this->assertEquals("read", $childMenu->getCapability());
        $this->assertEquals('parent-menu_child-menu', $childMenu->getSlug());
        $this->assertEquals($childHandler, $childMenu->getHandler());
        $this->assertEquals('', $childMenu->getRouteName());
        $this->assertEquals('parent-menu', $childMenu->getParentSlug());

        $this->assertInstanceOf(\WPWCore\Menu\Menu::class, $childMenu2);
        $this->assertEquals($childTitle, $childMenu2->getPageTitle());
        $this->assertEquals($childTitle, $childMenu2->getName());
        $this->assertEquals($capability, $childMenu2->getCapability());
        $this->assertEquals('parent-menu_child-menu', $childMenu2->getSlug());
        $this->assertEquals($childHandler, $childMenu2->getHandler());
        $this->assertEquals('parent-route.xyz', $childMenu2->getRouteName());
        $this->assertEquals('parent-menu', $childMenu2->getParentSlug());

        $this->assertEquals("http://localhost/wp-admin/admin.php?page=parent-menu", Menu::getUrl("parent-route"));
        $this->assertEquals("http://localhost/wp-admin/admin.php?page=parent-menu_child-menu", Menu::getUrl("parent-route.xyz"));


    }




}



