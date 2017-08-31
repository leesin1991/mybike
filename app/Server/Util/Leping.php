<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Server\Util;

class Leping extends Storage {

    public function login($ws, $data) {
        $device = $this->dbase()->device()->where(array('imei' => $data['DAT'][3]))->fetch();
        if ($device) {
            $device->update(array(
                'mtime' => time(),
                'action' => 1,
            ));
        } else {
            $entity = $this->dbase()->entity()->insert(array(
                'agent_id' => 1,
                'entsn_id' => 1,
                'sn' => $data['DAT'][0]
            ));
            $device = $this->dbase()->device()->insert(array(
                'imei' => $data['DAT'][3],
                'binding' => $entity['id'],
                'cases' => 1,
                'client_id' => 1,
                'agent_id' => 1,
                'static_revenue' => 0,
                'dynamic_revenue' => 0,
                'revenue' => 0,
                'lat' => 0,
                'lng' => 0,
                'ctime' => time(),
                'mtime' => time(),
                'action' => 1,
                'status' => 0,
            ));
        }
        $cache = array(
            'fid' => $data['FID'],
            'did' => $device['id'],
            'lid' => $data['DAT'][0],
            'sta' => 1,
        );
        $this->redis()->setex('LOCKER_' . $device['id'], 3600 * 24, json_encode($cache));
        $this->redis()->setex('FRAME_' . $data['FID'], 3600 * 24, json_encode($cache));
        echo "LOGIN ... OK\r\n";
        $echo = $this->pang('OK', $data);
        $ws->push($data['FID'], $echo);
    }

    public function location($ws, $data) {
        echo "LOCATION ... OK\r\n";
        $echo = $this->pang('OK', $data);
        $ws->push($data['FID'], $echo);
    }

    public function unlock($ws, $data) {
        $json = $this->redis()->lpop('waitting_unlock');
        if ($json) {
            $item = json_decode($json, true);
            $json = $this->redis()->get('LOCKER_' . $item['did']);
            if ($json) {
                $lock = json_decode($json, true);
                $echo = "OPEN ".(time()%1000)."\r\n" . date('Y-m-d H:i:s') . ',' . '15000301197' . ',' . date('dHis') . $lock['lid'] . ',' . md5(uniqid()) . ',' . "178\r\n";
                echo $echo;
                $ws->push($lock['fid'], $echo);
                echo "UNLOCK ... OK\r\n";
            }
        }
    }

    public function open($ws, $data) {
        echo "OPEN ... OK\r\n";
        $echo = $this->pang('OK', $data);
        $ws->push($data['FID'], $echo);
    }

    public function record($ws, $data) {
        echo "RECORD ... OK\r\n";
        $echo = $this->pang('OK', $data);
        $ws->push($data['FID'], $echo);
    }

    protected function pang($status, $data) {
        if ($status == 'OK') {
            $orgin = $data['CMD'] . ' ' . $data['MID'] . "\r\n0 OK";
        } elseif ($status == 'FM') {
            $orgin = $data['CMD'] . ' ' . $data['MID'] . "\r\n-1 FM";
        } elseif ($status == 'EX') {
            $orgin = $data['CMD'] . ' ' . $data['MID'] . "\r\n-2 EX";
        } elseif ($status == 'VE') {
            $orgin = $data['CMD'] . ' ' . $data['MID'] . "\r\n-3 VE";
        } else {
            $orgin = $data['CMD'] . ' ' . $data['MID'] . "\r\n-4 OT";
        }
        return $orgin;
    }

}
