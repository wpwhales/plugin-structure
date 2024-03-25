<?php

namespace Tests\Assets;


use WPWCore\Assets\Manager;
use WPWCore\Http\Request;
use function WPWCore\asset;
use function WPWCore\bundle;
use function WPWCore\bundleCSS;
use function WPWCore\bundleJS;
use function WPWCore\view;

class AssetDirectiveTest extends \WP_UnitTestCase
{

    public function set_up()
    {
        parent::set_up();

        $paths = \WPWCore\config("view.paths");
        $paths[] = __DIR__;

        $config = \WPWCore\app("config");
        $config->set("view.paths", $paths);

        $sessionInstance = $this->getMockBuilder(Request::class)->onlyMethods(["session","get"])->getMock();

        $sessionInstance->expects($this->exactly(1))->method("session")->will($this->returnSelf());

        $sessionInstance->expects($this->exactly(1))->method("get")->will($this->returnValue(null));
        $this->app["request"] = $sessionInstance;

        $view = $this->app["view"];

        $this->app["assets"];
        $this->app["config"]->set("assets.manifests.plugin.path", __DIR__ . "/fixtures");
        $this->app["config"]->set("assets.manifests.plugin.assets", __DIR__ . "/fixtures/manifest.json");
        $this->app["config"]->set("assets.manifests.plugin.bundles", __DIR__ . "/fixtures/entrypoints.json");
        $this->app["config"]->set("assets.manifests.plugin.bundles", __DIR__ . "/fixtures/entrypoints.json");
        $this->app["config"]->set("assets.manifests.plugin.url", site_url("wp-content/uploads/wpwhales/assets/fixtures"));


        $this->app->singleton('assets', function () {
            return new Manager($this->app->make('config')->get('assets'));
        });

    }

    public function test_asset_directive_output(){




        $view = $this->app["view"];

        $content = $view->make("test")->render();

        $this->assertStringContainsString(site_url("wp-content/uploads/wpwhales/assets/fixtures/123321.js"),$content);

    }

}
