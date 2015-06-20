<?php

namespace Wetzel\Datamapper\Versioning;

use Illuminate\Database\Eloquent\Builder as BaseBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;

class Builder extends BaseBuilder {

    /**
     * Insert a new record into the database.
     *
     * @param  array  $values
     * @return bool
     */
    public function insert(array $values)
    {
        // get version values & values
        $versionValues = $this->getVersionValues($values);
        $values = $this->getValues($values);

        // set version and latest_version
        $values[$this->model->getLatestVersionColumn()] = 1;
        $versionValues[$this->model->getVersionColumn()] = 1;

        // insert main table record
        if(! $this->query->insert($values)) {
            return false;
        }

        // insert version table record
        $db = $this->model->getConnection();
        return $db->table($this->model->getVersionTable())->insert($versionValues);
    }

    /**
     * Update a record in the database.
     *
     * @param  array  $values
     * @return int
     */
    public function update(array $values)
    {
        // update timestamps
        $values = $this->addUpdatedAtColumn($values);

        // get version values & values
        $versionValues = $this->getVersionValues($values);
        $values = $this->getValues($values);

        // get records
        $affectedRecords = $this->getAffectedRecordsWithVersion();

        // update main table records
        if(! $this->query->increment($this->model->getLatestVersionColumn(), 1, $values)) {
            return false;
        }

        // update version table records
        $db = $this->model->getConnection();
        foreach($affectedRecords as $id => $version) {
            $affectedRecordVersionValues = array_merge([
                $this->model->getVersionKeyName() => $id,
                $this->model->getVersionColumn() => $version+1
            ], $versionValues);

            if(! $db->table($this->model->getVersionTable())->insert($affectedRecordVersionValues)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Delete a record from the database.
     *
     * @return mixed
     */
    public function delete()
    {
        if (isset($this->onDelete)) {
            return call_user_func($this->onDelete, $this);
        }

        $this->forceDelete();
    }

    /**
     * Run the default delete function on the builder.
     *
     * @return mixed
     */
    public function forceDelete()
    {
        // get version values & values
        $versionValues = $this->getVersionValues($values);
        $values = $this->getValues($values);

        // get records
        $affectedRecords = $this->getAffectedRecordsWithVersion();

        // delete main table records
        if(! $this->query->delete()) {
            return false;
        }

        // delete version table records
        $db = $this->model->getConnection();
        return $db->table($this->model->getVersionTable())
            ->whereIn($this->model->getVersionKeyName(), array_flip($affectedRecords))
            ->delete();
    }

    /**
     * Get affected records.
     *
     * @return array
     */
    protected function getAffectedRecordsWithUpdatedVersion()
    {
        // model only
        if ($this->model->getKey()) {
            $records = [
                $this->model->getKey() => $this->model->{$this->model->getLatestVersionColumn()}
            ];
        }

        // mass assignment
        else {
            $records = [];
            foreach($this->query->get() as $record) {
                $records[$record->{$this->model->getKeyName()}] = $record->{$this->model->getLatestVersionColumn()};
            }
        }

        return $records;
    }

    /**
     * Get affected ids.
     *
     * @param  array  $values
     * @return array
     */
    protected function getValues(array $values)
    {
        $versionedKeys = $this->model->getVersionedAttributeNames();

        $array = array_diff_key($values, array_flip($versionedKeys));

        return $array;
    }

    /**
     * Get affected ids.
     *
     * @param  array  $values
     * @return array
     */
    protected function getVersionValues(array $values)
    {
        $versionedKeys = $this->model->getVersionedAttributeNames();

        $array = array_intersect_key($values, array_flip($versionedKeys));
        $array[$this->model->getVersionKeyName()] = $this->model->getKey();

        return $array;
    }

    /**
     * Add the "updated at" column to an array of values.
     *
     * @param  array  $values
     * @return array
     */
    protected function addUpdatedAtColumn(array $values)
    {
        if (! $this->model->usesTimestamps() && ! $this->model->usesVersionTimestamps()) return $values;

        if ($this->model->usesTimestamps()) {
            $column = $this->model->getUpdatedAtColumn();
        }

        if ($this->model->usesVersionTimestamps()) {
            $column = $this->model->getUpdatedAtColumn();
        }

        return array_add($values, $column, $this->model->freshTimestampString());
    }

    /**
     * Get updatedAt timestamp.
     *
     * @return array
     */
    protected function getUpdatedAtTimestamp()
    {
        if ( ! $model->usesTimestamps()) return $values;

        return [$this->model->getUpdatedAtColumn() => $this->model->freshTimestampString()];
    }

    /**
     * Get deletedAt timestamp.
     *
     * @return array
     */
    protected function getDeletedAtTimestamp()
    {
        return [$this->model->getQualifiedDeletedAtColumn() => $this->model->freshTimestampString()];
    }

}