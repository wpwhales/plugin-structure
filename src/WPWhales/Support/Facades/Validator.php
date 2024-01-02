<?php

namespace WPWhales\Support\Facades;

/**
 * @method static \WPWhales\Validation\Validator make(array $data, array $rules, array $messages = [], array $attributes = [])
 * @method static array validate(array $data, array $rules, array $messages = [], array $attributes = [])
 * @method static void extend(string $rule, \Closure|string $extension, string|null $message = null)
 * @method static void extendImplicit(string $rule, \Closure|string $extension, string|null $message = null)
 * @method static void extendDependent(string $rule, \Closure|string $extension, string|null $message = null)
 * @method static void replacer(string $rule, \Closure|string $replacer)
 * @method static void includeUnvalidatedArrayKeys()
 * @method static void excludeUnvalidatedArrayKeys()
 * @method static void resolver(\Closure $resolver)
 * @method static \WPWhales\Contracts\Translation\Translator getTranslator()
 * @method static \WPWhales\Validation\PresenceVerifierInterface getPresenceVerifier()
 * @method static void setPresenceVerifier(\WPWhales\Validation\PresenceVerifierInterface $presenceVerifier)
 * @method static \WPWhales\Contracts\Container\Container|null getContainer()
 * @method static \WPWhales\Validation\Factory setContainer(\WPWhales\Contracts\Container\Container $container)
 *
 * @see \WPWhales\Validation\Factory
 */
class Validator extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'validator';
    }
}
