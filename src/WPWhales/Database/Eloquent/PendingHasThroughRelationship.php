<?php

namespace WPWhales\Database\Eloquent;

use BadMethodCallException;
use WPWhales\Database\Eloquent\Relations\HasMany;
use WPWhales\Support\Str;

class PendingHasThroughRelationship
{
    /**
     * The root model that the relationship exists on.
     *
     * @var \WPWhales\Database\Eloquent\Model
     */
    protected $rootModel;

    /**
     * The local relationship.
     *
     * @var \WPWhales\Database\Eloquent\Relations\HasMany|\WPWhales\Database\Eloquent\Relations\HasOne
     */
    protected $localRelationship;

    /**
     * Create a pending has-many-through or has-one-through relationship.
     *
     * @param  \WPWhales\Database\Eloquent\Model  $rootModel
     * @param  \WPWhales\Database\Eloquent\Relations\HasMany|\WPWhales\Database\Eloquent\Relations\HasOne  $localRelationship
     */
    public function __construct($rootModel, $localRelationship)
    {
        $this->rootModel = $rootModel;

        $this->localRelationship = $localRelationship;
    }

    /**
     * Define the distant relationship that this model has.
     *
     * @param  string|(callable(\WPWhales\Database\Eloquent\Model): (\WPWhales\Database\Eloquent\Relations\HasOne|\WPWhales\Database\Eloquent\Relations\HasMany))  $callback
     * @return \WPWhales\Database\Eloquent\Relations\HasManyThrough|\WPWhales\Database\Eloquent\Relations\HasOneThrough
     */
    public function has($callback)
    {
        if (is_string($callback)) {
            $callback = fn () => $this->localRelationship->getRelated()->{$callback}();
        }

        $distantRelation = $callback($this->localRelationship->getRelated());

        if ($distantRelation instanceof HasMany) {
            return $this->rootModel->hasManyThrough(
                $distantRelation->getRelated()::class,
                $this->localRelationship->getRelated()::class,
                $this->localRelationship->getForeignKeyName(),
                $distantRelation->getForeignKeyName(),
                $this->localRelationship->getLocalKeyName(),
                $distantRelation->getLocalKeyName(),
            );
        }

        return $this->rootModel->hasOneThrough(
            $distantRelation->getRelated()::class,
            $this->localRelationship->getRelated()::class,
            $this->localRelationship->getForeignKeyName(),
            $distantRelation->getForeignKeyName(),
            $this->localRelationship->getLocalKeyName(),
            $distantRelation->getLocalKeyName(),
        );
    }

    /**
     * Handle dynamic method calls into the model.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        if (Str::startsWith($method, 'has')) {
            return $this->has(Str::of($method)->after('has')->lcfirst()->toString());
        }

        throw new BadMethodCallException(sprintf(
            'Call to undefined method %s::%s()', static::class, $method
        ));
    }
}
