<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Controller\Main\Part;

class Footer extends Part {

    protected $options = [];
    protected $tmpl = '/footer.html';

    public function actions() {
        return true;
    }
}
