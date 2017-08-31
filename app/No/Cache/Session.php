<?php

namespace No\Cache;

use No\Cache;

class Session implements Cache {

    function load($key) {
        if (!isset($_SESSION["NotORM"][$key])) {
            return null;
        }
        return $_SESSION["NotORM"][$key];
    }

    function save($key, $data) {
        $_SESSION["NotORM"][$key] = $data;
    }

}