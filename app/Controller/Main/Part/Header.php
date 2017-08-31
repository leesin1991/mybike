<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Controller\Main\Part;

class Header extends Part {

    protected $options = [];
    protected $tmpl = '/header.html';

    public function actions() {
        $authed = $this->authed();
        $acl = new \Util\ACL($this->app());
        $this->options['data']['menus'] = $acl->menu($authed['role_id']); 
        return true;
    }
}
