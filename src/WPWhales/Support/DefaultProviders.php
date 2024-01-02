<?php

namespace WPWhales\Support;

class DefaultProviders
{
    /**
     * The current providers.
     *
     * @var array
     */
    protected $providers;

    /**
     * Create a new default provider collection.
     *
     * @return void
     */
    public function __construct(?array $providers = null)
    {
        $this->providers = $providers ?: [
            \WPWhales\Auth\AuthServiceProvider::class,
            \WPWhales\Broadcasting\BroadcastServiceProvider::class,
            \WPWhales\Bus\BusServiceProvider::class,
            \WPWhales\Cache\CacheServiceProvider::class,
            \WPWhales\Foundation\Providers\ConsoleSupportServiceProvider::class,
            \WPWhales\Cookie\CookieServiceProvider::class,
            \WPWhales\Database\DatabaseServiceProvider::class,
            \WPWhales\Encryption\EncryptionServiceProvider::class,
            \WPWhales\Filesystem\FilesystemServiceProvider::class,
            \WPWhales\Foundation\Providers\FoundationServiceProvider::class,
            \WPWhales\Hashing\HashServiceProvider::class,
            \WPWhales\Mail\MailServiceProvider::class,
            \WPWhales\Notifications\NotificationServiceProvider::class,
            \WPWhales\Pagination\PaginationServiceProvider::class,
            \WPWhales\Pipeline\PipelineServiceProvider::class,
            \WPWhales\Queue\QueueServiceProvider::class,
            \WPWhales\Redis\RedisServiceProvider::class,
            \WPWhales\Auth\Passwords\PasswordResetServiceProvider::class,
            \WPWhales\Session\SessionServiceProvider::class,
            \WPWhales\Translation\TranslationServiceProvider::class,
            \WPWhales\Validation\ValidationServiceProvider::class,
            \WPWhales\View\ViewServiceProvider::class,
        ];
    }

    /**
     * Merge the given providers into the provider collection.
     *
     * @param  array  $providers
     * @return static
     */
    public function merge(array $providers)
    {
        $this->providers = array_merge($this->providers, $providers);

        return new static($this->providers);
    }

    /**
     * Replace the given providers with other providers.
     *
     * @param  array  $items
     * @return static
     */
    public function replace(array $replacements)
    {
        $current = collect($this->providers);

        foreach ($replacements as $from => $to) {
            $key = $current->search($from);

            $current = is_int($key) ? $current->replace([$key => $to]) : $current;
        }

        return new static($current->values()->toArray());
    }

    /**
     * Disable the given providers.
     *
     * @param  array  $providers
     * @return static
     */
    public function except(array $providers)
    {
        return new static(collect($this->providers)
                ->reject(fn ($p) => in_array($p, $providers))
                ->values()
                ->toArray());
    }

    /**
     * Convert the provider collection to an array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->providers;
    }
}
