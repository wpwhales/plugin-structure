<?php

namespace WPWhales\Support\Facades;

/**
 * @method static \WPWCore\Menu\Menu add(string $pageTitle, $handler, $capability = 'read')
 * @method static void remove(string $slug, string $submenuSlug = null)
 * @method static array getMenus()
 * @method static \WPWCore\Menu\Menu group(string $pageTitle, $handler, $capability, callable $fn, $name = "")
 * @method static string getUrl($name)
 * @method static void register()
 * @method static array getSlugs()
 * @method static array getRouteNames()
 * @method static string getUniqueSlug(string $slug)
 * @method static \WPWCore\Menu\Menu|null currentGroup()
 *
 * @see \WPWCore\Menu\MenuBuilder
 */

class Menu extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return "menu";
    }

}
