<?php

namespace WPWhales\Support\Facades;

use WPWhales\Contracts\Auth\Access\Gate as GateContract;

/**
 * @method static bool has(string|array $ability)
 * @method static \WPWhales\Auth\Access\Response allowIf(\WPWhales\Auth\Access\Response|\Closure|bool $condition, string|null $message = null, string|null $code = null)
 * @method static \WPWhales\Auth\Access\Response denyIf(\WPWhales\Auth\Access\Response|\Closure|bool $condition, string|null $message = null, string|null $code = null)
 * @method static \WPWhales\Auth\Access\Gate define(string $ability, callable|array|string $callback)
 * @method static \WPWhales\Auth\Access\Gate resource(string $name, string $class, array|null $abilities = null)
 * @method static \WPWhales\Auth\Access\Gate policy(string $class, string $policy)
 * @method static \WPWhales\Auth\Access\Gate before(callable $callback)
 * @method static \WPWhales\Auth\Access\Gate after(callable $callback)
 * @method static bool allows(iterable|string $abilities, array|mixed $arguments = [])
 * @method static bool denies(iterable|string $abilities, array|mixed $arguments = [])
 * @method static bool check(iterable|string $abilities, array|mixed $arguments = [])
 * @method static bool any(iterable|string $abilities, array|mixed $arguments = [])
 * @method static bool none(iterable|string $abilities, array|mixed $arguments = [])
 * @method static \WPWhales\Auth\Access\Response authorize(string $ability, array|mixed $arguments = [])
 * @method static \WPWhales\Auth\Access\Response inspect(string $ability, array|mixed $arguments = [])
 * @method static mixed raw(string $ability, array|mixed $arguments = [])
 * @method static mixed getPolicyFor(object|string $class)
 * @method static \WPWhales\Auth\Access\Gate guessPolicyNamesUsing(callable $callback)
 * @method static mixed resolvePolicy(object|string $class)
 * @method static \WPWhales\Auth\Access\Gate forUser(\WPWhales\Contracts\Auth\Authenticatable|mixed $user)
 * @method static array abilities()
 * @method static array policies()
 * @method static \WPWhales\Auth\Access\Gate defaultDenialResponse(\WPWhales\Auth\Access\Response $response)
 * @method static \WPWhales\Auth\Access\Gate setContainer(\WPWhales\Contracts\Container\Container $container)
 * @method static \WPWhales\Auth\Access\Response denyWithStatus(int $status, string|null $message = null, int|null $code = null)
 * @method static \WPWhales\Auth\Access\Response denyAsNotFound(string|null $message = null, int|null $code = null)
 *
 * @see \WPWhales\Auth\Access\Gate
 */
class Gate extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return GateContract::class;
    }
}
