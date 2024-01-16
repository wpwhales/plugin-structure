<?php

namespace WPWCore\Database\Eloquent;

interface Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \WPWCore\Database\Eloquent\Builder  $builder
     * @param  \WPWCore\Database\Eloquent\Model  $model
     * @return void
     */
    public function apply(Builder $builder, Model $model);
}
