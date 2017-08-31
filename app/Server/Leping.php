<?php

ini_set("display_errors", "On");
error_reporting(E_ALL);
spl_autoload_register(function($class) {
    include_once dirname(dirname(__FILE__)) . '/' . str_replace("\\", "/", $class) . '.php';
});

use Server\Util\Handle;

$hdl = new Handle();
$ws = new swoole_websocket_server("0.0.0.0", 8848);
$ws->set(array(
    'task_worker_num' => 40,
    'task_ipc_mode' => 3
));
$ws->on('Start', function($ws) use($hdl) {
    $hdl->start($ws);
});
$ws->on('WorkerStart', function($ws) use($hdl) {
    $hdl->workstart($ws);
});
$ws->on('Open', function($ws, $request) use($hdl) {
    $hdl->open($ws, $request);
});
$ws->on('Message', function($ws, $frame) use($hdl) {
    $hdl->message($ws, $frame);
});
$ws->on('Task', function($ws, $task_id, $from_id, $frame) use($hdl) {
    $hdl->task($ws, $task_id, $from_id, $frame);
});
$ws->on('Finish', function($ws, $task_id, $frame) use($hdl) {
    $hdl->finish($ws, $task_id, $frame);
});
$ws->on('Close', function($ws, $fd) use($hdl) {
    $hdl->close($ws, $fd);
});
$ws->start();
