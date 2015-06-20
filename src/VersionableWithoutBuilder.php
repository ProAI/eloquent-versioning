<?php

namespace Wetzel\Datamapper\Versioning;

trait VersionableWithoutBuilder
{
    /**
     * The model's versioned attributes (temporary used for saving).
     *
     * @var array
     */
    protected $versionAttributes = [];

    /**
     * Boot the soft deleting trait for a model.
     *
     * @return void
     */
    public static function bootVersionableWithoutBuilder()
    {
        static::addGlobalScope(new VersioningScope);

        // saving event
        static::saving(function($model) {
            $versionAttributes = [];
            $attributes = $model->getAttributes();
            $versioned = $model->getVersioned();

            // temporary put versioned attributes into $model->versionAttributes
            $versionAttributes = array_intersect_key($attributes, array_flip($versioned));
            $attributes = array_diff_key($attributes, array_flip($versioned));

            // update version fields
            if (! $model->exists) {
                $attributes['latest_version'] = 1;
            } else {
                $attributes['latest_version']++;
            }
            $versionAttributes['version'] = $attributes['latest_version'];

            $model->setRawVersionAttributes($versionAttributes);
            $model->setRawAttributes($attributes);
        });

        // saved event
        static::saved(function($model) {
            $versionAttributes = $model->getVersionAttributes();
            $attributes = $model->getAttributes();

            // put versioned attributes back into $model->attributes
            $attributes = array_merge($attributes, $versionAttributes);

            $db = $model->getConnection();

            // update all versions in case primary key value has been changed
            if ($model->getOriginal($model->getKeyName()) != $model->getKey()) {
                $db->table($model->getVersionTable())
                    ->where($model->getVersionKeyName(), '=', $model->getOriginal($model->getKeyName()))
                    ->update([$model->getVersionKeyName() => $model->getKey()]);
            }

            // insert new version
            $versionAttributes[$model->getVersionKeyName()] = $model->getKey();
            $db->table($model->getVersionTable())->insert($versionAttributes);

            $model->setRawVersionAttributes([]);
            $model->setRawAttributes($attributes);
        });
    }

    /**
     * Create a new model instance that is existing.
     *
     * @param  array  $attributes
     * @param  string|null  $connection
     * @return static
     */
    public function newFromBuilder($attributes = array(), $connection = null)
    {
        // hide ref_id from model, because ref_id == id
        $attributes = array_except((array) $attributes, $this->getVersionKeyName());

        return parent::newFromBuilder($attributes, $connection);
    }

    /**
     * Determine if the model uses normal timestamps.
     *
     * @return bool
     */
    /*public function usesTimestamps()
    {
        $updatedAt = $this->getUpdatedAtColumn();

        return $this->timestamps && (! isset($this->versioned) || ! in_array($updatedAt, $this->versioned));
    }*/

    /**
     * Determine if the model uses versionable timestamps.
     *
     * @return bool
     */
    /*public function usesVersionableTimestamps()
    {
        $updatedAt = $this->getUpdatedAtColumn();

        return $this->timestamps && (isset($this->versioned) && in_array($updatedAt, $this->versioned));
    }*/

    /**
     * Update version values.
     *
     * @return void
     */
    /*public function updateVersion()
    {
        if (! $model->exists) {
            $this->{$this->getLatestVersionColumn()} = 1;
        } else {
            $this->{$this->getLatestVersionColumn()}++;
        }

        $this->{$this->getVersionColumn()} = $this->{$this->getLatestVersionColumn()};
    }*/

    /**
     * Get the names of the attributes that are versioned.
     *
     * @return array
     */
    public function getVersionedAttributeNames()
    {
        return (! empty($this->versioned)) ? $this->versioned : [];
    }

    /**
     * Get the version key name.
     *
     * @return string
     */
    public function getVersionKeyName()
    {
        return 'ref_' . $this->getKeyName();
    }

    /**
     * Get the version table associated with the model.
     *
     * @return string
     */
    public function getVersionTable()
    {
        return $this->getTable() . '_version';
    }

    /**
     * Get the table qualified version key name.
     *
     * @return string
     */
    public function getQualifiedVersionKeyName()
    {
        return $this->getVersionTable().'.'.$this->getVersionKeyName();
    }

    /**
     * Get the name of the "latest version" column.
     *
     * @return string
     */
    public function getLatestVersionColumn()
    {
        return defined('static::VERSION') ? static::LATEST_VERSION : 'latest_version';
    }

    /**
     * Get the fully qualified "latest version" column.
     *
     * @return string
     */
    public function getQualifiedLatestVersionColumn()
    {
        return $this->getTable().'.'.$this->getLatestVersionColumn();
    }

    /**
     * Get the name of the "version" column.
     *
     * @return string
     */
    public function getVersionColumn()
    {
        return defined('static::VERSION') ? static::VERSION : 'version';
    }

    /**
     * Get the fully qualified "version" column.
     *
     * @return string
     */
    public function getQualifiedVersionColumn()
    {
        return $this->getVersionTable().'.'.$this->getVersionColumn();
    }

}
