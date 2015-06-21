<?php

namespace ProAI\Versioning;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ScopeInterface;
use Illuminate\Database\Eloquent\SoftDeletingScope as BaseSoftDeletingScope;

class SoftDeletingScope extends BaseSoftDeletingScope implements ScopeInterface {

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

        $builder->onDelete(function(Builder $builder)
        {
            $model = $builder->getModel();

            dd($model);

            $column = $this->getDeletedAtColumn($builder);

            // assume that we are in a model context if model exists == true
            if ($model->exists) {

            }

            // assume that we are in a query instance context if model exists == false
            else {

            }

            // versionable soft deletes
            if ($model->usesVersionableSoftDeletes()) {
                foreach($builder->get() as $model) {
                    $model->update([
                        $column => $builder->getModel()->freshTimestampString(),
                    ]);
                }

                return $builder;
            }

            // normal soft deletes, no new version
            else {
                return $builder->getQuery()->update(array(
                    $column => $builder->getModel()->freshTimestampString(),
                ));
            }
        });
    }

}
