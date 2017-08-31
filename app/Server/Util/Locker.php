<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Server\Util;

abstract class Locker extends Storage {

    public abstract function start(\swoole_websocket_server $serv);
    public abstract function workstart(\swoole_websocket_server $serv);
    public abstract function open(\swoole_websocket_server $ws, $request);
    public abstract function message(\swoole_websocket_server $ws, $frame);
    public abstract function task(\swoole_websocket_server $ws, $task_id, $from_id, $frame);
    public abstract function finish(\swoole_websocket_server $ws, $task_id, $frame);
    public abstract function close(\swoole_websocket_server $ws, $fd);

}
