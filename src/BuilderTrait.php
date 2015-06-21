<?php

namespace ProAI\Versioning;

use Exception;

trait BuilderTrait
{
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

        // set version, ref_id and latest_version
        $values[$this->model->getLatestVersionColumn()] = 1;
        $versionValues[$this->model->getVersionKeyName()] = $this->model->getKey();
        $versionValues[$this->model->getVersionColumn()] = 1;

        // insert main table record
        if (! $this->query->insert($values)) {
            return false;
        }

        // insert version table record
        $db = $this->model->getConnection();
        return $db->table($this->model->getVersionTable())->insert($versionValues);
    }

    /**
     * Insert a new record and get the value of the primary key.
     *
     * @param  array   $values
     * @param  string  $sequence
     * @return int
     */
    public function insertGetId(array $values, $sequence = null)
    {
        // get version values & values
        $versionValues = $this->getVersionValues($values);
        $values = $this->getValues($values);

        // set version and latest_version
        $values[$this->model->getLatestVersionColumn()] = 1;
        $versionValues[$this->model->getVersionColumn()] = 1;

        // insert main table record
        if (! $id = $this->query->insertGetId($values, $sequence)) {
            return false;
        }

        // set ref_id
        $versionValues[$this->model->getVersionKeyName()] = $id;

        // insert version table record
        $db = $this->model->getConnection();
        if (! $db->table($this->model->getVersionTable())->insert($versionValues)) {
            return false;
        }

        return $id;
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
        $affectedRecords = $this->getAffectedRecords();

        // update main table records
        if (! $this->query->increment($this->model->getLatestVersionColumn(), 1, $values)) {
            return false;
        }

        // update version table records
        $db = $this->model->getConnection();
        foreach ($affectedRecords as $record) {
            // get versioned values from record
            foreach($this->model->getVersionedAttributeNames() as $key) {
                $recordVersionValues[$key] = (isset($versionValues[$key])) ? $versionValues[$key] : $record->{$key};
            }

            // merge versioned values from record and input
            $recordVersionValues = array_merge($recordVersionValues, $versionValues);

            // set version and ref_id
            $recordVersionValues[$this->model->getVersionKeyName()] = $record->{$this->model->getKeyName()};
            $recordVersionValues[$this->model->getVersionColumn()] = $record->{$this->model->getVersionColumn()}+1;

            // insert new version
            if(! $db->table($this->model->getVersionTable())->insert($recordVersionValues)) {
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
        // get records
        $affectedRecords = $this->getAffectedRecords();
        $ids = array_map(function($record) {
            return $record->{$this->model->getKeyName()};
        }, $affectedRecords);

        // delete main table records
        if (! $this->query->delete()) {
            return false;
        }

        // delete version table records
        $db = $this->model->getConnection();
        return $db->table($this->model->getVersionTable())
            ->whereIn($this->model->getVersionKeyName(), $ids)
            ->delete();
    }

    /**
     * Get affected records.
     *
     * @return array
     */
    protected function getAffectedRecords()
    {
        // model only
        if ($this->model->getKey()) {
            $records = [$this->model];
        }

        // mass assignment
        else {
            $records = $this->query->get();
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
        $array = [];

        $versionedKeys = array_merge(
            $this->model->getVersionedAttributeNames(),
            [$this->model->getLatestVersionColumn(), $this->model->getVersionColumn(), $this->model->getVersionKeyName()]
        );

        foreach ($values as $key => $value) {
            if (! $this->isVersionedKey($key, $versionedKeys)) {
                $array[$key] = $value;
            }
        }

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
        $array = [];

        $versionedKeys = $this->model->getVersionedAttributeNames();

        foreach ($values as $key => $value) {
            if ($newKey = $this->isVersionedKey($key, $versionedKeys)) {
                $array[$newKey] = $value;
            }
        }

        return $array;
    }

    /**
     * Check if key is in versioned keys.
     *
     * @param  string  $key
     * @param  array  $versionedKeys
     * @return string|null
     */
    protected function isVersionedKey($key, array $versionedKeys)
    {
        $keyFractions = explode(".",$key);

        if (count($keyFractions) > 2) {
            throw new Exception("Key '".$key."' has too many fractions.");
        }

        if (count($keyFractions) == 1 && in_array($keyFractions[0], $versionedKeys)) {
            return $keyFractions[0];
        }

        if (count($keyFractions) == 2 && $keyFractions[0] == $this->model->getVersionTable() && in_array($keyFractions[1], $versionedKeys)) {
            return $keyFractions[1];
        }

        return null;
    }

}