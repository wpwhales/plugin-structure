<?php

namespace WPWCore\Menu;

use WPWhales\Support\Str;

class Menu
{
    protected string $pageTitle = '';
    protected string $name = '';
    protected string $routeName = '';
    protected string $parentRouteName = '';
    protected string $capability = '';
    protected string $slug = '';
    protected AbstractMenu $handler;
    protected ?int $position = null;
    protected string $icon = '';
    protected string $parentSlug = '';

    protected bool $hasSubMenus = false;

    public function __construct(string $pageTitle, $handler, $capability)
    {
        $this->pageTitle = $pageTitle;
        $this->capability = $capability;
        $this->slug = $this->toUrlParam($pageTitle);
        $this->setHandler($handler);
        $this->name = $pageTitle;


    }


    public function hasSubMenu()
    {
        return $this->hasSubMenus;
    }

    public function isParent()
    {

        $this->hasSubMenus = true;

        return $this;
    }

    public function parentRouteName($name)
    {

        $this->parentRouteName = $name;

    }

    public function routeName($name)
    {
        $this->routeName = $name;

        if (!empty($this->parentRouteName) && $name) {
            $this->routeName = $this->parentRouteName . "." . $name;
        }

        return $this;

    }

    public function getRouteName()
    {

        return $this->routeName;

    }

    public function name(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function capability(string $capability): self
    {
        $this->capability = $capability;

        return $this;
    }

    public function slug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function position(int $position): self
    {
        $this->position = $position;

        return $this;
    }

    public function icon(string $icon): self
    {
        $this->icon = $icon;

        return $this;
    }

    public function parentSlug(string $slug): self
    {
        $this->parentSlug = $slug;

        return $this;
    }

    public function getPageTitle(): string
    {
        return $this->pageTitle;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getCapability(): string
    {
        return $this->capability;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getHandler()
    {
        return $this->handler;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function getIcon(): string
    {
        return $this->icon;
    }

    public function getParentSlug(): string
    {
        return $this->parentSlug;
    }

    public function hasParent(): bool
    {
        return !empty($this->parentSlug);
    }

    protected function setName($name): string
    {


        return $name;
    }

    protected function setHandler($handler): self
    {

        if (!is_string($handler)) {
            throw new MenuException("The handler must be a string");
        }

        if (!class_exists($handler)) {
            throw new MenuException("The class $handler does not exist");
        }
        $instance = new $handler();

        if (!is_a($instance, AbstractMenu::class)) {
            throw new MenuException("The class $handler must be a instance of " . AbstractMenu::class);
        }

        if (!method_exists($handler, "print")) {
            throw new MenuException("The method print does not exist in $handler");
        }

        $this->handler = $instance;

        return $this;
    }

    protected function toSnakeCase($string): string
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $string));
    }

    protected function toUrlParam(string $string): string
    {
        return \WPWhales\Support\Facades\Menu::getUniqueSlug($string);
    }
}