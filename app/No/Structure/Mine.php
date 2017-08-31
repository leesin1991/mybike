<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace No\Structure;

class Mine extends Convention {
    
    public function __construct($primary = 'id', $foreign = '%s_id', $table = '%s', $prefix = '') {
        parent::__construct($primary, $foreign, $table, $prefix);
    }

    public function getReferencedTable($name, $table) {
        if ($name == "created_by" || $name == "modified_by") {
            return "user";
        } else if ($name == "parent") {
            return $table;
        }
        return parent::getReferencedTable($name, $table);
    }

}
