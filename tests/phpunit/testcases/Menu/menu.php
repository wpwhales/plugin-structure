<?php


use WPWhales\Support\Facades\Menu;

Menu::add("XYZ", [\Tests\Menu\MenuHandlerExtendingInterface::class,"render"], "read")->routeName("xyz");
