<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Controller\Main\Part;

use Controller\Main\Controller;

abstract class Part extends Controller {

    protected $options = [];
    protected $tmpl = '/404.html';

    public function __construct($options = []) {
        $this->options = $options;
        parent::__construct();
    }

    protected function render($tmpl, $data = array()) {
        ob_start();
        extract($data);
        include($this->app()->rootpath() . '/app/Tmpl/Main/Part' . $tmpl);
        return ob_get_clean();
    }

    public function html() {
        $this->actions();
        return $this->render($this->tmpl, $this->options['data']);
    }

}
