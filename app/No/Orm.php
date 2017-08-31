<?php

namespace No;
use No\Structure\Convention;

class Orm extends Abstractic {

    /** Create database representation
     * @param PDO
     * @param NotORM_Structure or null for new NotORM_Structure_Convention
     * @param NotORM_Cache or null for no cache
     */
    function __construct(\PDO $connection, Structure $structure = null, Cache $cache = null) {
        $this->connection = $connection;
        $this->driver = $connection->getAttribute(\PDO::ATTR_DRIVER_NAME);
        if (!isset($structure)) {
            $structure = new Convention;
        }
        $this->structure = $structure;
        $this->cache = $cache;
    }

    /** Get table data to use as $db->table[1]
     * @param string
     * @return NotORM_Result
     */
    function __get($table) {
        return new Result($this->structure->getReferencingTable($table, ''), $this, true);
    }

    /** Set write-only properties
     * @return null
     */
    function __set($name, $value) {
        if ($name == "debug" || $name == "freeze" || $name == "rowClass") {
            $this->$name = $value;
        }
        if ($name == "transaction") {
            switch (strtoupper($value)) {
                case "BEGIN": return $this->connection->beginTransaction();
                case "COMMIT": return $this->connection->commit();
                case "ROLLBACK": return $this->connection->rollback();
            }
        }
    }

    /** Get table data
     * @param string
     * @param array (["condition"[, array("value")]]) passed to NotORM_Result::where()
     * @return NotORM_Result
     */
    function __call($table, array $where) {
        $return = new Result($this->structure->getReferencingTable($table, ''), $this);
        if ($where) {
            call_user_func_array(array($return, 'where'), $where);
        }
        return $return;
    }

}
