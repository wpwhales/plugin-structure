<?php

namespace WPWCore\Database\Eloquent\Relations;

use WPWhales\Contracts\Database\Eloquent\SupportsPartialRelations;
use WPWCore\Database\Eloquent\Builder;
use WPWCore\Database\Eloquent\Collection;
use WPWCore\Database\Eloquent\Model;
use WPWCore\Database\Eloquent\Relations\Concerns\CanBeOneOfMany;
use WPWCore\Database\Eloquent\Relations\Concerns\ComparesRelatedModels;
use WPWCore\Database\Eloquent\Relations\Concerns\SupportsDefaultModels;
use WPWCore\Database\Query\JoinClause;

class HasOne extends HasOneOrMany implements SupportsPartialRelations
{
    use ComparesRelatedModels, CanBeOneOfMany, SupportsDefaultModels;

    /**
     * Get the results of the relationship.
     *
     * @return mixed
     */
    public function getResults()
    {
        if (is_null($this->getParentKey())) {
            return $this->getDefaultFor($this->parent);
        }

        return $this->query->first() ?: $this->getDefaultFor($this->parent);
    }

    /**
     * Initialize the relation on a set of models.
     *
     * @param  array  $models
     * @param  string  $relation
     * @return array
     */
    public function initRelation(array $models, $relation)
    {
        foreach ($models as $model) {
            $model->setRelation($relation, $this->getDefaultFor($model));
        }

        return $models;
    }

    /**
     * Match the eagerly loaded results to their parents.
     *
     * @param  array  $models
     * @param  \WPWCore\Database\Eloquent\Collection  $results
     * @param  string  $relation
     * @return array
     */
    public function match(array $models, Collection $results, $relation)
    {
        return $this->matchOne($models, $results, $relation);
    }

    /**
     * Add the constraints for an internal relationship existence query.
     *
     * Essentially, these queries compare on column names like "whereColumn".
     *
     * @param  \WPWCore\Database\Eloquent\Builder  $query
     * @param  \WPWCore\Database\Eloquent\Builder  $parentQuery
     * @param  array|mixed  $columns
     * @return \WPWCore\Database\Eloquent\Builder
     */
    public function getRelationExistenceQuery(Builder $query, Builder $parentQuery, $columns = ['*'])
    {
        if ($this->isOneOfMany()) {
            $this->mergeOneOfManyJoinsTo($query);
        }

        return parent::getRelationExistenceQuery($query, $parentQuery, $columns);
    }

    /**
     * Add constraints for inner join subselect for one of many relationships.
     *
     * @param  \WPWCore\Database\Eloquent\Builder  $query
     * @param  string|null  $column
     * @param  string|null  $aggregate
     * @return void
     */
    public function addOneOfManySubQueryConstraints(Builder $query, $column = null, $aggregate = null)
    {
        $query->addSelect($this->foreignKey);
    }

    /**
     * Get the columns that should be selected by the one of many subquery.
     *
     * @return array|string
     */
    public function getOneOfManySubQuerySelectColumns()
    {
        return $this->foreignKey;
    }

    /**
     * Add join query constraints for one of many relationships.
     *
     * @param  \WPWCore\Database\Query\JoinClause  $join
     * @return void
     */
    public function addOneOfManyJoinSubQueryConstraints(JoinClause $join)
    {
        $join->on($this->qualifySubSelectColumn($this->foreignKey), '=', $this->qualifyRelatedColumn($this->foreignKey));
    }

    /**
     * Make a new related instance for the given model.
     *
     * @param  \WPWCore\Database\Eloquent\Model  $parent
     * @return \WPWCore\Database\Eloquent\Model
     */
    public function newRelatedInstanceFor(Model $parent)
    {
        return $this->related->newInstance()->setAttribute(
            $this->getForeignKeyName(), $parent->{$this->localKey}
        );
    }

    /**
     * Get the value of the model's foreign key.
     *
     * @param  \WPWCore\Database\Eloquent\Model  $model
     * @return mixed
     */
    protected function getRelatedKeyFrom(Model $model)
    {
        return $model->getAttribute($this->getForeignKeyName());
    }
}
