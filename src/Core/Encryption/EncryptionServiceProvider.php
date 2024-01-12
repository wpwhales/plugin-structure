<?php

namespace WPWCore\Encryption;


use WPWhales\Support\ServiceProvider;
use WPWhales\Support\Str;

class EncryptionServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerEncrypter();
    }

    /**
     * Register the encrypter.
     *
     * @return void
     */
    protected function registerEncrypter()
    {
        $this->app->singleton('encrypter', function ($app) {
            $config = $app->make('config')->get('app');

            return new Encrypter($this->parseKey($config), $config['cipher']);
        });
    }



    /**
     * Parse the encryption key.
     *
     * @param  array  $config
     * @return string
     */
    protected function parseKey(array $config)
    {
        if (Str::startsWith($key = $this->key($config), $prefix = 'base64:')) {
            $key = base64_decode(Str::after($key, $prefix));
        }

        return $key;
    }

    /**
     * Extract the encryption key from the given configuration.
     *
     * @param  array  $config
     * @return string
     *
     * @throws \WPWCore\Encryption\MissingAppKeyException
     */
    protected function key(array $config)
    {
        return \WPWCore\Support\tap($config['key'], function ($key) {
            if (empty($key)) {
                throw new MissingAppKeyException;
            }
        });
    }
}
