<?php

namespace No;

abstract class Abstractic {

    protected $connection, $driver, $structure, $cache;
    protected $notORM, $table, $primary, $rows, $referenced = array();
    protected $debug = false;
    protected $freeze = false;
    protected $rowClass = '\No\Row';

    protected function access($key, $delete = false) {
        
    }

}
