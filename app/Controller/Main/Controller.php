<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Controller\Main;

use \App\Main;

abstract class Controller {

    protected static $app;

    public function __construct() {
        if (!self::$app) {
            self::$app = new Main();
        }
        $this->actions();
    }

    public function app() {
        return self::$app;
    }

    public function no() {
        return $this->app()->no();
    }

    public function authed() {
        return $this->app()->authed();
    }

    public abstract function actions();
    
    public function getCateDropList($table, $sid = 0, $pid = 0, $blank = "", $where = array()) {
        $where['parent_id'] = $pid;
        $blank = "&nbsp;&nbsp;&nbsp;&nbsp;" . $blank;
        $rs = $this->no()->$table()->where($where)->select('');
        $list = $this->app()->iterator_array($rs);
        foreach ($list as $vul) {
            $str .= "<option ";
            if ($sid == $vul["id"]) {
                $str .= " selected ";
            }
            $str .= "value=\"" . $vul["id"] . "\">" . $blank . "|-" . $vul["name"] . "</option>";
            $str .= $this->getCateDropList($table, $sid, $vul["id"], $blank);
        }
        return $str;
    }
    
    public function getRandom($length=6,$num=null)
    {
        $str = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"; 
        if($num)$str = "0123456789";
        $len = strlen($str)-1;
        for($i=0 ; $i<$length; $i++){
            $s .=  $str[rand(0,$len)];
        }
        return $s;
    }
    
    public function validateMobile($mobile)
    {
        if (preg_match('/^1(3[0-9]|4[57]|5[0-35-9]|7[0135678]|8[0-9])\\d{8}$/', $mobile)) {
            return true;
        }   
    }
    
    public function run() {
        self::$app->run();
    }

}
