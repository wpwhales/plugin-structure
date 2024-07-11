<?php

namespace Tests\Menu;

use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use WPWCore\Exceptions\WPWException;
use WPWCore\Exceptions\WPWExceptionInterface;
use WPWCore\Menu\AbstractMenu;
use WPWCore\Menu\MenuBuilder;
use WPWCore\Menu\MenuException;
use WPWCore\Routing\Controller;
use WPWCore\View\View;
use WPWhales\Http\Request;
use WPWhales\Support\Facades\Menu;
use WPWhales\Testing\TestResponse;


class AdminMenuHandlerTest extends \WP_UnitTestCase
{

    public function set_up()
    {
        parent::set_up();

        $paths = \WPWCore\config("view.paths");
        $paths[] = __DIR__;
        $config = \WPWCore\app("config");
        $config->set("view.paths", $paths);


    }


    public function testIsMenuRegistered()
    {
        global $wp_filter;
        $app = $this->app;
        $this->app->withAdminMenuHandler();


        $this->assertIsInt(has_action("admin_menu", [$app, "loadAdminMenus"]));
    }

    /**
     * Test creating a single menu.
     *
     * @return void
     */
    public function testCreateSingleMenu()
    {
        $this->app->withAdminMenuHandler();

        $handler = MenuHandlerExtendingAbstract::class;
        $capability = 'manage_options';
        $pageTitle = 'Test Menu';

        $menu = Menu::add($pageTitle, $handler, $capability)->routeName("xyz");


        $this->assertInstanceOf(\WPWCore\Menu\Menu::class, $menu);
        $this->assertEquals($pageTitle, $menu->getPageTitle());
        $this->assertEquals($pageTitle, $menu->getName());
        $this->assertEquals($capability, $menu->getCapability());
        $this->assertEquals('test-menu', $menu->getSlug());
        $this->assertEquals($handler, $menu->getHandler()[0]);

        $this->assertEquals("http://localhost/wp-admin/admin.php?page=test-menu", Menu::getUrl("xyz"));

    }

    /**
     * Test creating a grouped menu.
     *
     * @return void
     */
    public function testCreateGroupedMenu()
    {
        $this->app->withAdminMenuHandler();

        $parentHandler = MenuHandlerExtendingAbstract::class;
        $childHandler = MenuHandlerExtendingAbstract::class;
        $capability = 'manage_options';
        $parentTitle = 'Parent Menu';
        $childTitle = 'Child Menu';

        $parentMenu = Menu::group($parentTitle, $parentHandler, $capability, function ($builder) use ($childTitle, $capability, $childHandler) {
            $builder->add($childTitle, $childHandler);
            $builder->add($childTitle, $childHandler, $capability)->routeName("xyz");
        }, 'parent-route');

        //looks like it's on 1 index as per our code
        $childMenu = Menu::getMenus()[1];
        $childMenu2 = Menu::getMenus()[2];
        $this->assertInstanceOf(\WPWCore\Menu\Menu::class, $parentMenu);
        $this->assertEquals($parentTitle, $parentMenu->getPageTitle());
        $this->assertEquals($parentTitle, $parentMenu->getName());
        $this->assertEquals($capability, $parentMenu->getCapability());
        $this->assertEquals('parent-menu', $parentMenu->getSlug());
        $this->assertEquals($parentHandler, $parentMenu->getHandler()[0]);
        $this->assertEquals('parent-route', $parentMenu->getRouteName());

        $this->assertInstanceOf(\WPWCore\Menu\Menu::class, $childMenu);
        $this->assertEquals($childTitle, $childMenu->getPageTitle());
        $this->assertEquals($childTitle, $childMenu->getName());
        $this->assertEquals("read", $childMenu->getCapability());
        $this->assertEquals('parent-menu_child-menu', $childMenu->getSlug());
        $this->assertEquals($childHandler, $childMenu->getHandler()[0]);
        $this->assertEquals('', $childMenu->getRouteName());
        $this->assertEquals('parent-menu', $childMenu->getParentSlug());

        $this->assertInstanceOf(\WPWCore\Menu\Menu::class, $childMenu2);
        $this->assertEquals($childTitle, $childMenu2->getPageTitle());
        $this->assertEquals($childTitle, $childMenu2->getName());
        $this->assertEquals($capability, $childMenu2->getCapability());
        $this->assertEquals('parent-menu_child-menu', $childMenu2->getSlug());
        $this->assertEquals($childHandler, $childMenu2->getHandler()[0]);
        $this->assertEquals('parent-route.xyz', $childMenu2->getRouteName());
        $this->assertEquals('parent-menu', $childMenu2->getParentSlug());

        $this->assertEquals("http://localhost/wp-admin/admin.php?page=parent-menu", Menu::getUrl("parent-route"));
        $this->assertEquals("http://localhost/wp-admin/admin.php?page=parent-menu_child-menu", Menu::getUrl("parent-route.xyz"));


    }


    public function testHandlerDoesNotExtend()
    {
        $this->app->withAdminMenuHandler();

        $handler = TestMenuHandlerWithoutExtendingAbstract::class;
        $capability = 'manage_options';
        $pageTitle = 'Test Menu';

        $this->expectException(MenuException::class);
        $this->expectExceptionMessage("The class $handler must be a instance of " . AbstractMenu::class);

        $menu = Menu::add($pageTitle, $handler, $capability);

    }

    public function testHandlerOutput()
    {
        $this->app->withAdminMenuHandler();

        $handler = MenuHandlerExtendingAbstract::class;
        $capability = 'manage_options';
        $pageTitle = 'Test Menu';


        $menu = Menu::add($pageTitle, $handler, $capability);

        $handler = $menu->getHandler();

        $instance = new $handler[0]();

        ob_start();
        $instance->{$handler[1]}();
        $content = ob_get_clean();


        $this->assertStringContainsString("Menu", $content);
    }


    public function testFilePathMenuIntegration()
    {
        $this->app->withAdminMenuHandler(__DIR__ . "/menu.php");

        do_action("admin_menu");
        $menu = Menu::getMenus()[0];

        $this->assertInstanceOf(\WPWCore\Menu\Menu::class, $menu);
        $this->assertEquals("XYZ", $menu->getPageTitle());
        $this->assertEquals("XYZ", $menu->getName());
        $this->assertEquals('read', $menu->getCapability());
        $this->assertEquals('xyz', $menu->getSlug());
        $this->assertEquals(MenuHandlerExtendingAbstract::class, $menu->getHandler()[0]);
        $this->assertEquals('xyz', $menu->getRouteName());
    }


}

class TestMenuHandlerWithoutExtendingAbstract
{

    public function handler()
    {
        echo 123;
    }
}

class MenuHandlerExtendingAbstract extends AbstractMenu
{
    protected function render(): View
    {
        return \WPWCore\view("xyz");
    }

}



