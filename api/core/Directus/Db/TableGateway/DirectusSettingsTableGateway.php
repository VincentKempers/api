<?php

namespace Directus\Db\TableGateway;

use Directus\Acl\Acl;
use Directus\Db\TableGateway\AclAwareTableGateway;
use Zend\Db\Adapter\AdapterInterface;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Select;

class DirectusSettingsTableGateway extends AclAwareTableGateway {

    public static $_tableName = "directus_settings";

    public function __construct(Acl $acl, AdapterInterface $adapter) {
        parent::__construct($acl, self::$_tableName, $adapter);
    }

    public function fetchAll($selectModifier = null) {
        $sql = new Sql($this->adapter);
        $select = $sql->select()->from($this->table);
        $select->columns(array('collection','name','value'))
            ->order('collection');
        // Fetch row
        $rowset = $this->selectWith($select);
        $rowset = $rowset->toArray();
        $result = array();
        foreach($rowset as $row) {
            $collection = $row['collection'];
            if(!array_key_exists($collection, $result)) {
                $result[$collection] = array();
            }
            $result[$collection][$row['name']] = $row['value'];
        }
        return $result;
    }

    public function fetchCollection($collection, $requiredKeys = array()) {
        $select = new Select($this->table);
        $select->where->equalTo('collection', $collection);
        $rowset = $this->selectWith($select)->toArray();
        $result = array();
        foreach($rowset as $row) {
            $result[$row['name']] = $row['value'];
        }
        if(count(array_diff($requiredKeys, array_keys($result)))) {
            throw new \Exception("The following keys must be defined in the `$collection` settings collection: " . implode(", ", $requiredKeys));
        }
        return $result;
    }

    public function fetchByCollectionAndName($collection, $name) {
        $select = new Select($this->table);
        $select->limit(1);
        $select
            ->where
                ->equalTo('collection', $collection)
                ->equalTo('name', $name);
        $rowset = $this->selectWith($select);
        $result = $rowset->current();
        if(false === $result) {
            throw new \Exception("Required `directus_setting` with collection `$collection` and name `$name` not found.");
        }
        return $result;
    }

}
