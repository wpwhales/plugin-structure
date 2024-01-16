<?php

namespace WPWCore\Session;

use WPWhales\Contracts\Auth\Guard;
use WPWhales\Contracts\Container\Container;
use WPWCore\Database\ConnectionInterface;
use WPWCore\Database\QueryException;
use WPWhales\Support\Arr;
use WPWhales\Support\Carbon;
use WPWhales\Support\InteractsWithTime;
use SessionHandlerInterface;

class DatabaseSessionHandler implements ExistenceAwareInterface, SessionHandlerInterface
{
    use InteractsWithTime;

    /**
     * The database connection instance.
     *
     * @var \WPWCore\Database\ConnectionInterface
     */
    protected $connection;

    /**
     * The name of the session table.
     *
     * @var string
     */
    protected $table;

    /**
     * The number of minutes the session should be valid.
     *
     * @var int
     */
    protected $minutes;

    /**
     * The container instance.
     *
     * @var \WPWhales\Contracts\Container\Container|null
     */
    protected $container;

    /**
     * The existence state of the session.
     *
     * @var bool
     */
    protected $exists;

    /**
     * Create a new database session handler instance.
     *
     * @param  \WPWCore\Database\ConnectionInterface  $connection
     * @param  string  $table
     * @param  int  $minutes
     * @param  \WPWhales\Contracts\Container\Container|null  $container
     * @return void
     */
    public function __construct(ConnectionInterface $connection, $table, $minutes, Container $container = null)
    {
        $this->table = $table;
        $this->minutes = $minutes;
        $this->container = $container;
        $this->connection = $connection;
    }

    /**
     * {@inheritdoc}
     *
     * @return bool
     */
    public function open($savePath, $sessionName): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     *
     * @return bool
     */
    public function close(): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     *
     * @return string|false
     */
    public function read($sessionId): string|false
    {
        $session = (object) $this->getQuery()->find($sessionId);

        if ($this->expired($session)) {
            $this->exists = true;

            return '';
        }

        if (isset($session->payload)) {
            $this->exists = true;

            return base64_decode($session->payload);
        }

        return '';
    }

    /**
     * Determine if the session is expired.
     *
     * @param  \stdClass  $session
     * @return bool
     */
    protected function expired($session)
    {
        return isset($session->last_activity) &&
            $session->last_activity < Carbon::now()->subMinutes($this->minutes)->getTimestamp();
    }

    /**
     * {@inheritdoc}
     *
     * @return bool
     */
    public function write($sessionId, $data): bool
    {
        $payload = $this->getDefaultPayload($data);

        if (! $this->exists) {
            $this->read($sessionId);
        }

        if ($this->exists) {
            $this->performUpdate($sessionId, $payload);
        } else {
            $this->performInsert($sessionId, $payload);
        }

        return $this->exists = true;
    }

    /**
     * Perform an insert operation on the session ID.
     *
     * @param  string  $sessionId
     * @param  array<string, mixed>  $payload
     * @return bool|null
     */
    protected function performInsert($sessionId, $payload)
    {
        try {
            return $this->getQuery()->insert(Arr::set($payload, 'id', $sessionId));
        } catch (QueryException) {
            $this->performUpdate($sessionId, $payload);
        }
    }

    /**
     * Perform an update operation on the session ID.
     *
     * @param  string  $sessionId
     * @param  array<string, mixed>  $payload
     * @return int
     */
    protected function performUpdate($sessionId, $payload)
    {
        return $this->getQuery()->where('id', $sessionId)->update($payload);
    }

    /**
     * Get the default payload for the session.
     *
     * @param  string  $data
     * @return array
     */
    protected function getDefaultPayload($data)
    {
        $payload = [
            'payload' => base64_encode($data),
            'last_activity' => $this->currentTime(),
        ];

        if (! $this->container) {
            return $payload;
        }

        return \WPWCore\Support\tap($payload, function (&$payload) {
            $this->addUserInformation($payload)
                ->addRequestInformation($payload);
        });
    }

    /**
     * Add the user information to the session payload.
     *
     * @param  array  $payload
     * @return $this
     */
    protected function addUserInformation(&$payload)
    {
            $payload['user_id'] = get_current_user_id();


        return $this;
    }

    /**
     * Get the currently authenticated user's ID.
     *
     * @return mixed
     */
    protected function userId()
    {
        //TODO INTEGRATE THE GUARD LATER

        return get_current_user_id();
    }

    /**
     * Add the request information to the session payload.
     *
     * @param  array  $payload
     * @return $this
     */
    protected function addRequestInformation(&$payload)
    {
        if ($this->container->bound('request')) {
            $payload = array_merge($payload, [
                'ip_address' => $this->ipAddress(),
                'user_agent' => $this->userAgent(),
            ]);
        }

        return $this;
    }

    /**
     * Get the IP address for the current request.
     *
     * @return string|null
     */
    protected function ipAddress()
    {
        return $this->container->make('request')->ip();
    }

    /**
     * Get the user agent for the current request.
     *
     * @return string
     */
    protected function userAgent()
    {
        return substr((string) $this->container->make('request')->header('User-Agent'), 0, 500);
    }

    /**
     * {@inheritdoc}
     *
     * @return bool
     */
    public function destroy($sessionId): bool
    {
        $this->getQuery()->where('id', $sessionId)->delete();

        return true;
    }

    /**
     * {@inheritdoc}
     *
     * @return int
     */
    public function gc($lifetime): int
    {
        return $this->getQuery()->where('last_activity', '<=', $this->currentTime() - $lifetime)->delete();
    }

    /**
     * Get a fresh query builder instance for the table.
     *
     * @return \WPWCore\Database\Query\Builder
     */
    protected function getQuery()
    {
        return $this->connection->table($this->table);
    }

    /**
     * Set the application instance used by the handler.
     *
     * @param  \WPWhales\Contracts\Foundation\Application  $container
     * @return $this
     */
    public function setContainer($container)
    {
        $this->container = $container;

        return $this;
    }

    /**
     * Set the existence state for the session.
     *
     * @param  bool  $value
     * @return $this
     */
    public function setExists($value)
    {
        $this->exists = $value;

        return $this;
    }
}
