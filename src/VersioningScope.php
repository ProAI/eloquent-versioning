<?php

namespace ProAI\Versioning;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Query\JoinClause;

class VersioningScope implements Scope
{
    /**
     * All of the extensions to be added to the builder.
     *
     * @var array
     */
    protected $extensions = ['Version', 'AllVersions'];

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

            $builder->withoutGlobalScope($this)->join($model->getVersionTable(), function($join) use ($model, $version) {
                $join->on($model->getQualifiedKeyName(), '=', $model->getQualifiedVersionKeyName());
                $join->where($model->getQualifiedVersionColumn(), '=', $version);
            });

            return $builder;
        });
    }

    /**
     * Add the allVersions extension to the builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @return void
     */
    protected function addAllVersions(Builder $builder)
    {
        $builder->macro('allVersions', function(Builder $builder) {
            $model = $builder->getModel();

            $builder->withoutGlobalScope($this)->join($model->getVersionTable(), function($join) use ($model) {
                $join->on($model->getQualifiedKeyName(), '=', $model->getQualifiedVersionKeyName());
            });

            return $builder;
        });
    }

}
