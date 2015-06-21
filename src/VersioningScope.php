<?php

namespace ProAI\Versioning;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ScopeInterface;
use Illuminate\Database\Query\JoinClause;

class VersioningScope implements ScopeInterface {

    /**
     * All of the extensions to be added to the builder.
     *
     * @var array
     */
    protected $extensions = ['Version', 'GetAllVersions', 'FindAllVersions'];

    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        $builder->join($model->getVersionTable(), function($join) use ($model) {
            $join->on($model->getQualifiedKeyName(), '=', $model->getQualifiedVersionKeyName());
            $join->on($model->getQualifiedVersionColumn(), '=', $model->getQualifiedLatestVersionColumn());
        });

        $this->extend($builder);
    }

    /**
     * Remove the scope from the given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function remove(Builder $builder, Model $model)
    {
        $table = $model->getVersionTable();

        $query = $builder->getQuery();

        $query->joins = collect($query->joins)->reject(function($join) use ($table)
        {
            return $this->isVersionJoinConstraint($join, $table);
        })->values()->all();
    }

    /**
     * Extend the query builder with the needed functions.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @return void
     */
    public function extend(Builder $builder)
    {
        foreach ($this->extensions as $extension)
        {
            $this->{"add{$extension}"}($builder);
        }
    }

    /**
     * Add the version extension to the builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @return void
     */
    protected function addVersion(Builder $builder)
    {
        $builder->macro('version', function(Builder $builder, $version) {
            $model = $builder->getModel();

            $this->remove($builder, $builder->getModel());

            $builder->join($model->getVersionTable(), function($join) use ($model, $version) {
                $join->on($model->getQualifiedKeyName(), '=', $model->getQualifiedVersionKeyName());
                $join->where($model->getQualifiedVersionColumn(), '=', $version);
            });

            return $builder;
        });
    }

    /**
     * Add the version extension to the builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @return void
     */
    protected function addGetAllVersions(Builder $builder)
    {
        $builder->macro('getAllVersions', function(Builder $builder) {
            $model = $builder->getModel();

            $this->remove($builder, $builder->getModel());

            $builder->join($model->getVersionTable(), function($join) use ($model) {
                $join->on($model->getQualifiedKeyName(), '=', $model->getQualifiedVersionKeyName());
            });

            return $builder->get();
        });
    }

    /**
     * Add the version extension to the builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @return void
     */
    protected function addFindAllVersions(Builder $builder)
    {
        $builder->macro('findAllVersions', function(Builder $builder, $id) {
            $model = $builder->getModel();

            $this->remove($builder, $builder->getModel());

            $builder->getQuery()->where($model->getQualifiedKeyName(), '=', $id);

            $builder->join($model->getVersionTable(), function($join) use ($model) {
                $join->on($model->getQualifiedKeyName(), '=', $model->getQualifiedVersionKeyName());
            });

            return $builder->get();
        });
    }

    /**
     * Determine if the given join clause is a version constraint.
     *
     * @param  \Illuminate\Database\Query\JoinClause   $join
     * @param  string  $column
     * @return bool
     */
    protected function isVersionJoinConstraint(JoinClause $join, $table)
    {
        return $join->type == 'inner' && $join->table == $table;
    }

}
