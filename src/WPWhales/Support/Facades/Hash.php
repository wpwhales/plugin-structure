<?php

namespace WPWhales\Support\Facades;

/**
 * @method static \WPWCore\Hashing\BcryptHasher createBcryptDriver()
 * @method static \WPWCore\Hashing\ArgonHasher createArgonDriver()
 * @method static \WPWCore\Hashing\Argon2IdHasher createArgon2idDriver()
 * @method static array info(string $hashedValue)
 * @method static string make(string $value, array $options = [])
 * @method static bool check(string $value, string $hashedValue, array $options = [])
 * @method static bool needsRehash(string $hashedValue, array $options = [])
 * @method static bool isHashed(string $value)
 * @method static string getDefaultDriver()
 * @method static mixed driver(string|null $driver = null)
 * @method static \WPWCore\Hashing\HashManager extend(string $driver, \Closure $callback)
 * @method static array getDrivers()
 * @method static \WPWhales\Contracts\Container\Container getContainer()
 * @method static \WPWCore\Hashing\HashManager setContainer(\WPWhales\Contracts\Container\Container $container)
 * @method static \WPWCore\Hashing\HashManager forgetDrivers()
 *
 * @see \WPWCore\Hashing\HashManager
 * @see \WPWCore\Hashing\AbstractHasher
 */
class Hash extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'hash';
    }
}
