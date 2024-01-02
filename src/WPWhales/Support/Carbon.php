<?php

namespace WPWhales\Support;

use Carbon\Carbon as BaseCarbon;
use Carbon\CarbonImmutable as BaseCarbonImmutable;
use WPWhales\Support\Traits\Conditionable;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Uid\Ulid;

class Carbon extends BaseCarbon
{
    use Conditionable;

    /**
     * {@inheritdoc}
     */
    public static function setTestNow($testNow = null)
    {
        BaseCarbon::setTestNow($testNow);
        BaseCarbonImmutable::setTestNow($testNow);
    }

    /**
     * Create a Carbon instance from a given ordered UUID or ULID.
     *
     * @param  \Ramsey\Uuid\Uuid|\Symfony\Component\Uid\Ulid|string  $id
     * @return \WPWhales\Support\Carbon
     */
    public static function createFromId($id)
    {
        return Ulid::isValid($id)
            ? static::createFromInterface(Ulid::fromString($id)->getDateTime())
            : static::createFromInterface(Uuid::fromString($id)->getDateTime());
    }

    /**
     * Dump the instance and end the script.
     *
     * @param  mixed  ...$args
     * @return never
     */
    public function dd(...$args)
    {
        dd($this, ...$args);
    }

    /**
     * Dump the instance.
     *
     * @return $this
     */
    public function dump()
    {
        dump($this);

        return $this;
    }
}
