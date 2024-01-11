<?php

namespace WPWhales\Support\Facades;

use WPWhales\Contracts\Auth\Access\Gate as GateContract;

/**
 * @method static bool has(string|array $ability)
 * @method static \WPWCore\Auth\Access\Response allowIf(\WPWCore\Auth\Access\Response|\Closure|bool $condition, string|null $message = null, string|null $code = null)
 * @method static \WPWCore\Auth\Access\Response denyIf(\WPWCore\Auth\Access\Response|\Closure|bool $condition, string|null $message = null, string|null $code = null)
 * @method static \WPWCore\Auth\Access\Gate define(string $ability, callable|array|string $callback)
 * @method static \WPWCore\Auth\Access\Gate resource(string $name, string $class, array|null $abilities = null)
 * @method static \WPWCore\Auth\Access\Gate policy(string $class, string $policy)
 * @method static \WPWCore\Auth\Access\Gate before(callable $callback)
 * @method static \WPWCore\Auth\Access\Gate after(callable $callback)
 * @method static bool allows(iterable|string $abilities, array|mixed $arguments = [])
 * @method static bool denies(iterable|string $abilities, array|mixed $arguments = [])
 * @method static bool check(iterable|string $abilities, array|mixed $arguments = [])
 * @method static bool any(iterable|string $abilities, array|mixed $arguments = [])
 * @method static bool none(iterable|string $abilities, array|mixed $arguments = [])
 * @method static \WPWCore\Auth\Access\Response authorize(string $ability, array|mixed $arguments = [])
 * @method static \WPWCore\Auth\Access\Response inspect(string $ability, array|mixed $arguments = [])
 * @method static mixed raw(string $ability, array|mixed $arguments = [])
 * @method static mixed getPolicyFor(object|string $class)
 * @method static \WPWCore\Auth\Access\Gate guessPolicyNamesUsing(callable $callback)
 * @method static mixed resolvePolicy(object|string $class)
 * @method static \WPWCore\Auth\Access\Gate forUser(\WPWhales\Contracts\Auth\Authenticatable|mixed $user)
 * @method static array abilities()
 * @method static array policies()
 * @method static \WPWCore\Auth\Access\Gate defaultDenialResponse(\WPWCore\Auth\Access\Response $response)
 * @method static \WPWCore\Auth\Access\Gate setContainer(\WPWhales\Contracts\Container\Container $container)
 * @method static \WPWCore\Auth\Access\Response denyWithStatus(int $status, string|null $message = null, int|null $code = null)
 * @method static \WPWCore\Auth\Access\Response denyAsNotFound(string|null $message = null, int|null $code = null)
 *
 * @see \WPWCore\Auth\Access\Gate
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
