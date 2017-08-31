<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Server\Util;

class Storage {

    private static $db;
    private static $rd;
    private static $cc;

    public function dbase() {
        if (!self::$db) {
            $pdo = new \PDO('mysql:host=localhost;dbname=shared', 'root', '7&8&8^67HkjU', array(\PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8';"));
            self::$db = new \No\Orm($pdo);
        }
        return self::$db;
    }

    public function redis() {
        if (!self::$rd) {
            $rd = new \Redis();
            $rd->connect('127.0.0.1', 6379);
            self::$rd = $rd;
        }
        return self::$rd;
    }

    public function cache() {
        if (!self::$cc) {
            $cc = new \Memcached();
            $cc->addServer('127.0.0.1', 11211);
            self::$cc = $cc;
        }
        return self::$cc;
    }
    
}
