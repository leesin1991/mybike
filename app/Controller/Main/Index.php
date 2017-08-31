<?php

/*
 * ./wkhtmltox/bin/wkhtmltopdf localhost/test.html /tmp/test.pdf
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Controller\Main;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class Index extends Controller {

    public function actions() {
        $app = $this->app();
        $app->add(function (Request $req, Response $res, $next) use($app) {
            $app->setAgent($req);
            return $next($req, $res);
        });
        $this->app()->get('/', array($this, 'index'))->add(function (Request $req, Response $res, $next) use($app) {
            return $app->isLogin($req, $res, $next);
        })->setName('index');
        $this->app()->get('/index.html', array($this, 'indexGet'))->add(function (Request $req, Response $res, $next) use($app) {
            return $app->isLogin($req, $res, $next);
        })->setName('index.index');
        $this->app()->get('/logout.html', array($this, 'logoutGet'))->add(function (Request $req, Response $res, $next) use($app) {
            return $app->isLogin($req, $res, $next);
        })->setName('index.index');
        $this->app()->get('/account.json', array($this, 'loginAccountGet'))->add(function (Request $req, Response $res, $next) use($app) {
            return $app->isLogin($req, $res, $next);
        })->setName('index.index');
        $this->app()->get('/count.html', array($this, 'statisticsGet'));
        $this->app()->get('/login.html', array($this, 'loginGet'));
        $this->app()->post('/login.html', array($this, 'loginPost'));
        return $this;
    }

    public function index(Request $req, Response $res, $args) {
        header('Location: http://partner.baibaobike.com/index.html');
    }

    public function indexGet(Request $req, Response $res, $args) {
        $data = [
            'header' => [
                'data' => [
                    'title' => '首页 - 共享硬件云平台在线管理',
                ],
            ],
        ];
        $html = $this->app()->render('/index.html', $data);
        return $res->write($html);
    }

    public function loginAccountGet(Request $req, Response $res, $args) {
        $authed = $this->authed(); //获取用户登录信息
        $return = array(
            'status' => true,
            'data' => $authed,
        );
        return $res->withHeader('Content-type', 'application/json')->write(json_encode($return));
    }

    public function loginGet(Request $req, Response $res, $args) {
        $data = [
            'header' => [
                'data' => [
                    'title' => '登录 - 共享硬件云平台在线管理',
                ],
            ],
        ];
        $html = $this->app()->render('/login.html', $data);
        return $res->write($html);
    }

    public function loginPost(Request $req, Response $res, $args) {
        $post = $req->getParsedBody();
        $rs = $this->no()->agent()->where(array('account' => $post['username']))->fetch();
        if ($rs) {
            if (md5($post['password']) === $rs['passwd']) {
                $data = array(
                    'status' => true,
                    'errno' => '0',
                );
                $this->app()->ssset('authed', $rs->toArray());
            } else {
                $data = array(
                    'status' => false,
                    'errno' => '40015',
                    'errmsg' => "密码错误"
                );
            }
        } else {
            $data = array(
                'status' => false,
                'errno' => '40016',
                'errmsg' => "账户不存在"
            );
        }
        return $res->withHeader('Content-type', 'application/json')->write(json_encode($data));
    }

    public function logoutGet(Request $req, Response $res) {
        $this->app()->ssset('authed', false);
        $html = $this->app()->render('/login.html');
        return $res->write($html);
    }

    public function statisticsGet($param) {
        $clientNum = $this->app()->client()->select('')->count();
        print_r($clientNum);
    }

}
