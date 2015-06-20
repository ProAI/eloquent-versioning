<?php

namespace Wetzel\Datamapper\Versioning;

trait Versionable
{
    use VersionableWithoutBuilder;

    /**
     * Create a new Eloquent query builder for the model.
     *
     * @param  \Illuminate\Database\Query\Builder $query
     * @return \Wetzel\Datamapper\Versioning\Builder|static
     */
    public function newEloquentBuilder($query)
    {
        return new Builder($query);
    }
}
