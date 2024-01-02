<?php

namespace WPWhales\Database\Concerns;

use WPWhales\Support\Collection;

trait ExplainsQueries
{
    /**
     * Explains the query.
     *
     * @return \WPWhales\Support\Collection
     */
    public function explain()
    {
        $sql = $this->toSql();

        $bindings = $this->getBindings();

        $explanation = $this->getConnection()->select('EXPLAIN '.$sql, $bindings);

        return new Collection($explanation);
    }
}
