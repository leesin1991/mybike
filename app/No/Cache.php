<?php

namespace No;

interface Cache {

    /** Load stored data
     * @param string
     * @return mixed or null if not found
     */
    function load($key);

    /** Save data
     * @param string
     * @param mixed
     * @return null
     */
    function save($key, $data);
}
