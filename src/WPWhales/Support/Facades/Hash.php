<?php

namespace WPWhales\Support\Facades;

/**
 * @method static \WPWhales\Hashing\BcryptHasher createBcryptDriver()
 * @method static \WPWhales\Hashing\ArgonHasher createArgonDriver()
 * @method static \WPWhales\Hashing\Argon2IdHasher createArgon2idDriver()
 * @method static array info(string $hashedValue)
 * @method static string make(string $value, array $options = [])
 * @method static bool check(string $value, string $hashedValue, array $options = [])
 * @method static bool needsRehash(string $hashedValue, array $options = [])
 * @method static bool isHashed(string $value)
 * @method static string getDefaultDriver()
 * @method static mixed driver(string|null $driver = null)
 * @method static \WPWhales\Hashing\HashManager extend(string $driver, \Closure $callback)
 * @method static array getDrivers()
 * @method static \WPWhales\Contracts\Container\Container getContainer()
 * @method static \WPWhales\Hashing\HashManager setContainer(\WPWhales\Contracts\Container\Container $container)
 * @method static \WPWhales\Hashing\HashManager forgetDrivers()
 *
 * @see \WPWhales\Hashing\HashManager
 * @see \WPWhales\Hashing\AbstractHasher
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
