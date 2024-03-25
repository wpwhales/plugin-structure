<?php

namespace Tests\Assets;


use WPWCore\Assets\Manager;
use function WPWCore\asset;
use function WPWCore\bundle;
use function WPWCore\bundleCSS;
use function WPWCore\bundleJS;

class BundleFunctionsTest extends \WP_UnitTestCase
{

    public function set_up()
    {
        parent::set_up();


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

    public function test_enqueue_asset_load()
    {

        global $wp_scripts;

        $manifest = $this->app['assets.manifest'];

        $this->assertEquals(asset("script.js")->path(), __DIR__ . "/fixtures/script.hash.js");
        $this->assertEquals(asset("script.js")->__toString(), site_url("wp-content/uploads/wpwhales/assets/fixtures/script.hash.js"));




    }

    public function test_bundle_enqueue_and_localize_for_js()
    {

        global $wp_scripts;

        bundle("entrypoint")->enqueue()->localize("localizeData",["xyz"=>123]);
        $queue = $wp_scripts->queue;
        $this->assertIsArray($queue);
        $this->assertTrue(in_array("entrypoint/0", $queue));
        $this->assertTrue(in_array("entrypoint/1", $queue));

        $this->assertSame($wp_scripts->registered["entrypoint/0"]->extra["data"],'var localizeData = {"xyz":"123"};');


        bundle("entrypoint")->dequeue("entrypoint");
        $wp_scripts->remove(["entrypoint/0","entrypoint/1"]);

    }

    public function test_enqueue_css_files(){
        global $wp_styles,$wp_scripts;
        bundle("entrypoint")->enqueue();
        $queue = $wp_styles->registered["entrypoint/0"];

        $this->assertEquals($queue->src,site_url("wp-content/uploads/wpwhales/assets/fixtures/entrypoint.css"));
        bundle("entrypoint")->dequeue("entrypoint");
        $wp_scripts->remove(["entrypoint/0","entrypoint/1"]);


    }
//
    public function test_enqueue_dependency_js(){

        global $wp_scripts;

        bundle("entrypoint")->enqueueJs(false,["jquery"]);
        $queue = $wp_scripts->registered;


        $this->assertTrue(in_array("jquery",$queue["entrypoint/0"]->deps));
        $this->assertTrue(in_array("jquery",$queue["entrypoint/1"]->deps));
        $this->assertTrue(in_array("entrypoint/0",$queue["entrypoint/1"]->deps));
        bundle("entrypoint")->dequeue("entrypoint");
        $wp_scripts->remove(["entrypoint/0","entrypoint/1"]);
    }


    public function test_bundle_js_script_tag(){


        $scripts = bundleJS("entrypoint");
        // Regex to match src attributes
        preg_match_all("/src=\s*'([^']+)'/", $scripts, $srcMatches);
        // Regex to match id attributes
        preg_match_all("/id=\s*'([^']+)'/", $scripts, $idMatches);

        // Assuming you have defined these constants with the expected values
        $expectedSrc1 = site_url("wp-content/uploads/wpwhales/assets/fixtures/entrypoint1.js");
        $expectedSrc2 = site_url("wp-content/uploads/wpwhales/assets/fixtures/entrypoint2.js");
        $expectedId1 = 'WPWhales-js.entrypoint.0';
        $expectedId2 = 'WPWhales-js.entrypoint.1';
        $this->assertEquals($expectedSrc1, $srcMatches[1][0], "First script src does not match.");
        $this->assertEquals($expectedSrc2, $srcMatches[1][1], "Second script src does not match.");
        // Assert the id matches expected values
        $this->assertEquals($expectedId1, $idMatches[1][0], "First script id does not match.");
        $this->assertEquals($expectedId2, $idMatches[1][1], "Second script id does not match.");

    }

    public function test_bundle_css_link_tag(){


        $scripts = bundleCSS("entrypoint");
        // Regex to match src attributes
        preg_match_all("/href=\s*'([^']+)'/", $scripts, $srcMatches);
        // Regex to match id attributes
        preg_match_all("/id=\s*'([^']+)'/", $scripts, $idMatches);

        // Assuming you have defined these constants with the expected values
        $expectedSrc1 = site_url("wp-content/uploads/wpwhales/assets/fixtures/entrypoint.css");

        $expectedId1 = 'WPWhales-css.entrypoint.0';


        $this->assertEquals($expectedSrc1, $srcMatches[1][0], "First script src does not match.");

                // Assert the id matches expected values
        $this->assertEquals($expectedId1, $idMatches[1][0], "First script id does not match.");


    }

}
