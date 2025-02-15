<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Assets Manifest
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default asset manifest that should be used.
    | The "theme" manifest is recommended as the default as it cedes ultimate
    | authority of your application's assets to the theme.
    |
    */

    'default' => 'plugin',

    /*
    |--------------------------------------------------------------------------
    | Assets Manifests
    |--------------------------------------------------------------------------
    |
    | Manifests contain lists of assets that are referenced by static keys that
    | point to dynamic locations, such as a cache-busted location. We currently
    | support two types of manifest:
    |
    | assets: key-value pairs to match assets to their revved counterparts
    |
    | bundles: a series of entrypoints for loading bundles
    |
    */

    'manifests' => [
        'plugin' => [
            'path' => trim(WP_CONTENT_DIR,"/") ."/uploads/wpwhales/assets",
            'url' =>trim(WP_CONTENT_URL,"/") ."/uploads/wpwhales/assets",
            'assets' => trim(WP_CONTENT_DIR,"/")."/uploads/wpwhales/assets/manifest.json",
            'bundles' => trim(WP_CONTENT_DIR,"/")."/uploads/wpwhales/assets/entrypoints.json",
        ]
    ]
];
