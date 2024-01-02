<?php

namespace WPWhales\Database\Eloquent;

interface Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \WPWhales\Database\Eloquent\Builder  $builder
     * @param  \WPWhales\Database\Eloquent\Model  $model
     * @return void
     */
    public function apply(Builder $builder, Model $model);
}
