<?php


namespace WPWCore\Menu;

use WPWCore\Application;
use WPWhales\Routing\Exceptions\UrlGenerationException;
use WPWhales\Support\Str;

class MenuBuilder
{

    protected Application $app;
    protected ?Menu $group = null;

    protected array $menus = [];
    protected Menu $menu;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Add a new menu item to the menu builder.
     *
     * @param string $pageTitle The title of the menu page.
     * @param callable $handler The callback function that handles the menu page.
     * @param string $capability The capability required to access the menu page.
     * @return Menu The created menu object.
     */

    public function add(string $pageTitle, $handler, $capability = 'read'): Menu
    {


        $this->menu = new Menu($pageTitle, $handler, $capability);
        $this->menus[] = $this->menu;

        if ($this->group) {
            $this->menu->parentSlug($this->group->getSlug());
            $this->menu->slug($this->group->getSlug() . '_' . $this->menu->getSlug());
            $this->menu->parentRouteName($this->group->getRouteName());

        }

        return $this->menu;
    }

    /**
     * Remove a menu item or submenu item from the WordPress admin menu.
     *
     * @param string $slug The slug of the menu item to remove.
     * @param string|null $submenuSlug The slug of the submenu item to remove (optional).
     * @return void
     */

    public function remove(string $slug, string $submenuSlug = null)
    {
        if ($submenuSlug) {
            remove_submenu_page($slug, $submenuSlug);
        } else {
            remove_menu_page($slug);
        }
    }
    /**
     * Get the registered menus.
     *
     * @return array The array of registered menus.
     */
    public function getMenus()
    {
        return $this->menus;
    }


    /**
     * Create a group of menus with a parent menu and child menus.
     *
     * @param string $pageTitle The title of the parent menu page.
     * @param callable $handler The callback function that handles the parent menu page.
     * @param string $capability The capability required to access the parent menu page.
     * @param callable $fn The callback function that adds child menus.
     * @param string $name The name of the route for the parent menu (optional).
     * @return Menu The created parent menu object.
     */
    public function group(string $pageTitle, $handler, $capability, callable $fn, $name = "")
    {
        $group = $this->add($pageTitle, $handler, $capability)->isParent();
        if (!empty($name)) {
            $group->routeName($name);
        }
        $this->group = $group;
        $fn($this);
        $this->group = null;

        return $group;
    }

    /**
     * Get the URL for a registered menu by its name.
     *
     * @param string $name The name of the menu.
     * @return string The URL for the menu.
     * @throws UrlGenerationException If the menu name is not found.
     */
    public function getUrl($name)
    {

        $key = array_search($name, $this->getRouteNames());

        if ($key !== false) {
            //it means route found
            $menu = $this->menus[$key];

            return add_query_arg(
                [
                    'page' => $menu->getSlug()
                ],
                admin_url('admin.php')
            );
        }

        throw new UrlGenerationException("Unable to found any registered menu with this name [".$name."]");
    }

    /**
     * Register the menus with WordPress.
     *
     * @return void
     */

    public function register()
    {
        /**
         * @var Menu $menu
         */
        foreach ($this->menus as $menu) {
            if ($menu->hasParent()) {
                add_submenu_page(
                    $menu->getParentSlug(),
                    $menu->getPageTitle(),
                    $menu->getName(),
                    $menu->getCapability(),
                    $menu->getSlug(),
                    [$menu->getHandler(),"print"],
                    $menu->getPosition()
                );
            } else {
                add_menu_page(
                    $menu->getPageTitle(),
                    $menu->getName(),
                    $menu->getCapability(),
                    $menu->getSlug(),
                    [$menu->getHandler(),"print"],
                    $menu->getIcon(),
                    $menu->getPosition()
                );
            }
        }
    }

    /**
     * Get the slugs of the registered menus.
     *
     * @return array The array of menu slugs.
     */
    public function getSlugs()
    {
        $slugs = [];
        foreach ($this->menus as $i => $menu) {
            $slugs[$i] = $menu->getSlug();
        }

        return $slugs;
    }
    /**
     * Get the route names of the registered menus.
     *
     * @return array The array of menu route names.
     */
    public function getRouteNames()
    {
        $names = [];
        foreach ($this->menus as $menu) {
            $names[] = $menu->getRouteName();
        }

        return $names;
    }
    /**
     * Generate a unique slug for a given string.
     *
     * @param string $slug The base slug.
     * @return string The unique slug.
     */
    public function getUniqueSlug(string $slug)
    {


        // Initialize a counter
        $counter = 1;

        // Original slug to append numbers to
        $slug = Str::slug($slug, "-");

        // Loop until a unique slug is found
        while (in_array($slug, $this->getSlugs())) {
            // Append the counter to the original slug
            $slug = $slug . '-' . $counter;
            // Increment the counter
            $counter++;
        }

        // Return the unique slug
        return $slug;
    }
    /**
     * Get the current group menu, if any.
     *
     * @return Menu|null The current group menu, or null if no group is active.
     */
    public function currentGroup(): ?Menu
    {
        return $this->group;
    }
}