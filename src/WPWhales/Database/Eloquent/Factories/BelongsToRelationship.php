<?php

namespace WPWhales\Database\Eloquent\Factories;

use WPWhales\Database\Eloquent\Model;
use WPWhales\Database\Eloquent\Relations\MorphTo;

class BelongsToRelationship
{
    /**
     * The related factory instance.
     *
     * @var \WPWhales\Database\Eloquent\Factories\Factory|\WPWhales\Database\Eloquent\Model
     */
    protected $factory;

    /**
     * The relationship name.
     *
     * @var string
     */
    protected $relationship;

    /**
     * The cached, resolved parent instance ID.
     *
     * @var mixed
     */
    protected $resolved;

    /**
     * Create a new "belongs to" relationship definition.
     *
     * @param  \WPWhales\Database\Eloquent\Factories\Factory|\WPWhales\Database\Eloquent\Model  $factory
     * @param  string  $relationship
     * @return void
     */
    public function __construct($factory, $relationship)
    {
        $this->factory = $factory;
        $this->relationship = $relationship;
    }

    /**
     * Get the parent model attributes and resolvers for the given child model.
     *
     * @param  \WPWhales\Database\Eloquent\Model  $model
     * @return array
     */
    public function attributesFor(Model $model)
    {
        $relationship = $model->{$this->relationship}();

        return $relationship instanceof MorphTo ? [
            $relationship->getMorphType() => $this->factory instanceof Factory ? $this->factory->newModel()->getMorphClass() : $this->factory->getMorphClass(),
            $relationship->getForeignKeyName() => $this->resolver($relationship->getOwnerKeyName()),
        ] : [
            $relationship->getForeignKeyName() => $this->resolver($relationship->getOwnerKeyName()),
        ];
    }

    /**
     * Get the deferred resolver for this relationship's parent ID.
     *
     * @param  string|null  $key
     * @return \Closure
     */
    protected function resolver($key)
    {
        return function () use ($key) {
            if (! $this->resolved) {
                $instance = $this->factory instanceof Factory
                    ? ($this->factory->getRandomRecycledModel($this->factory->modelName()) ?? $this->factory->create())
                    : $this->factory;

                return $this->resolved = $key ? $instance->{$key} : $instance->getKey();
            }

            return $this->resolved;
        };
    }

    /**
     * Specify the model instances to always use when creating relationships.
     *
     * @param  \WPWhales\Support\Collection  $recycle
     * @return $this
     */
    public function recycle($recycle)
    {
        if ($this->factory instanceof Factory) {
            $this->factory = $this->factory->recycle($recycle);
        }

        return $this;
    }
}
