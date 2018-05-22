<?php

namespace ProAI\Versioning;

trait Versionable
{
    /**
     * Boot the versionable trait for a model.
     *
     * @return void
     */
    public static function bootVersionable()
    {
        static::addGlobalScope(new VersioningScope);
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
     * Create a new Eloquent query builder for the model.
     *
     * @param  \Illuminate\Database\Query\Builder $query
     * @return \ProAI\Versioning\Builder|static
     */
    public function newEloquentBuilder($query)
    {
        return new Builder($query);
    }

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
        return defined('static::LATEST_VERSION') ? static::LATEST_VERSION : 'latest_version';
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
