<?php

namespace WPWCore\Database\Eloquent\Factories;

use WPWCore\Database\Eloquent\Model;
use WPWCore\Database\Eloquent\Relations\BelongsToMany;
use WPWCore\Database\Eloquent\Relations\HasOneOrMany;
use WPWCore\Database\Eloquent\Relations\MorphOneOrMany;

class Relationship
{
    /**
     * The related factory instance.
     *
     * @var \WPWCore\Database\Eloquent\Factories\Factory
     */
    protected $factory;

    /**
     * The relationship name.
     *
     * @var string
     */
    protected $relationship;

    /**
     * Create a new child relationship instance.
     *
     * @param  \WPWCore\Database\Eloquent\Factories\Factory  $factory
     * @param  string  $relationship
     * @return void
     */
    public function __construct(Factory $factory, $relationship)
    {
        $this->factory = $factory;
        $this->relationship = $relationship;
    }

    /**
     * Create the child relationship for the given parent model.
     *
     * @param  \WPWCore\Database\Eloquent\Model  $parent
     * @return void
     */
    public function createFor(Model $parent)
    {
        $relationship = $parent->{$this->relationship}();

        if ($relationship instanceof MorphOneOrMany) {
            $this->factory->state([
                $relationship->getMorphType() => $relationship->getMorphClass(),
                $relationship->getForeignKeyName() => $relationship->getParentKey(),
            ])->create([], $parent);
        } elseif ($relationship instanceof HasOneOrMany) {
            $this->factory->state([
                $relationship->getForeignKeyName() => $relationship->getParentKey(),
            ])->create([], $parent);
        } elseif ($relationship instanceof BelongsToMany) {
            $relationship->attach($this->factory->create([], $parent));
        }
    }

    /**
     * Specify the model instances to always use when creating relationships.
     *
     * @param  \WPWhales\Support\Collection  $recycle
     * @return $this
     */
    public function recycle($recycle)
    {
        $this->factory = $this->factory->recycle($recycle);

        return $this;
    }
}
