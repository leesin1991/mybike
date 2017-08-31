<?php

namespace Server\Util;

class Handle extends Locker {

    protected $process;
    
    public function __construct() {
        $this->process = new Leping();
    }

    public function start(\swoole_websocket_server $serv) {
        echo "Server [" . date("H:i:s", time()) . "] : was started! \n";
    }

    public function workstart(\swoole_websocket_server $serv) {
        if ($serv->worker_id == 0) {
            $serv->tick(1000, function() use ($serv) {
                $data = [
                    'CMD' => 'UNLOCK'
                ];
                $serv->task($data);
            });
        }
    }

    public function open(\swoole_websocket_server $ws, $request) {
        
    }

    public function message(\swoole_websocket_server $ws, $frame) {
        echo $frame->data . "\r\n";
        $data = $this->frame($frame);
        $ws->task($data);
    }

    public function close(\swoole_websocket_server $ws, $fd) {
        echo date("H:i:s") . " : #" . $fd . " has gone  \n";
    }

    public function task(\swoole_websocket_server $ws, $task_id, $from_id, $data) {
        switch ($data['CMD']) {
            case 'LOGIN':
                return $this->process->login($ws,$data);
            case 'LOCATION':
                return $this->process->location($ws,$data);
            case 'UNLOCK':
                return $this->process->unlock($ws,$data);
            case 'OPEN':
                return $this->process->open($ws,$data);
            case 'RECORD':
                return $this->process->record($ws,$data);
        }
    }

    public function finish(\swoole_websocket_server $ws, $task_id, $frame) {
        echo "Task {$task_id} finish\n";
        echo "Result: {$frame->data}\n";
    }
    
    protected function frame($frame) {
        $rows = explode("\r\n", $frame->data);
        $exp = explode(' ', $rows[0]);
        return [
            'CMD' => $exp[0],
            'MID' => $exp[1],
            'DAT' => explode(' ', $rows[1]),
            'FID' => $frame->fd
        ];
    }
}
