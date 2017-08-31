<?php
use Controller\Main\Index;
use Controller\Main\User;
use Controller\Main\Balance;
use Controller\Main\Integral;
use Controller\Main\Role;
use Controller\Main\Power;
use Controller\Main\Device;
use Controller\Main\Entity;
use Controller\Main\Syslog;
use Controller\Main\Paylog;
use Controller\Main\Feedback;
use Controller\Main\Finance;
use Controller\Main\Article;
use Controller\Main\Category;
use Controller\Main\Agent;
use Controller\Main\Revenue;
use Controller\Main\Message;
use Controller\Main\Authed;
use Controller\Main\Test;
use Controller\Main\Coupon;
use Controller\Main\Cousn;
use Controller\Main\Profile;

spl_autoload_register(function($class) {
    include_once dirname(__FILE__) . '/' . str_replace("\\", "/", $class) . '.php';
});

$index = new Index();
new User();
new Balance();
new Integral();
new Role();
new Power();
new Device();
new Entity();
new Syslog();
new Paylog();
new Feedback();
new Finance();
new Article();
new Category();
new Agent();
new Revenue();
new Message();
new Authed();
new Test();
new Coupon();
new Cousn();
new Profile();
$index->run();

