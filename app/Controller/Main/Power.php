<?php

namespace Controller\Main;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class Power extends Controller {

    public function actions() {
        $app = $this->app();
        $this->app()->get('/power.html', array($this, 'powerGet'))->add(function (Request $req, Response $res, $next) use($app) {
            return $app->isLogin($req, $res, $next);
        })->setName('role.power.list');
        $this->app()->get('/power/list.json', array($this, 'listJson'))->add(function (Request $req, Response $res, $next) use($app) {
            return $app->isLogin($req, $res, $next);
        })->setName('role.power.list');
        $this->app()->get('/power/row.json', array($this, 'getRowJson'))->add(function (Request $req, Response $res, $next) use($app) {
            return $app->isLogin($req, $res, $next);
        })->setName('role.power.item');
        $this->app()->post('/power/add.html', array($this, 'addPost'))->add(function (Request $req, Response $res, $next) use($app) {
            return $app->isLogin($req, $res, $next);
        })->setName('role.power.edit');
        $this->app()->post('/power/del.html', array($this, 'delPost'))->add(function (Request $req, Response $res, $next) use($app) {
            return $app->isLogin($req, $res, $next);
        })->setName('role.power.done');
        return $this;
    }

    public function powerGet(Request $req, Response $res) {
        $data = [
            'header' => [
                'data' => [
                    'title' => '角色权限 - 共享硬件云平台在线管理',
                ],
            ],
        ];
        $html = $this->app()->render('/Role/power.html',$data);
        return $res->write($html);
    }
    
    public function listJson(Request $req, Response $res) {
        $get = $req->getQueryParams();
        $limit = 10;
        $page = isset($get['page']) ? $get['page'] : 1;
        $where = [];
        if ($get['id']) {
            $where['id'] = $get['id'];
        } else if ($get['role_id']) {
            $where['role_id'] = $get['role_id'];
        } else if ($get['status']) {
            $where['status'] = $get['status'];
        } else if ($get['name']) {
            $where['name'] = $get['name'];
        } 
        if ($get['order']) {
            $order = $get['order'];
        } else {
            $order = $get['order'] = 'id DESC';
        }
        $where['status'] = 0;
        $count = $this->no()->power()->select('')->where($where)->count('*');
        $num = ceil($count / $limit);
        $rs = $this->no()->power()->select('')->where($where)->order($order)->limit($limit, ($page - 1) * $limit);
        $data = $this->app()->iterator_array($rs);
        $return = array(
            'status' => true,
            'current' => $page,
            'total' => $num,
            'data' => $data,
        );
        return $res->withHeader('Content-type', 'application/json')->write(json_encode($return));
    }

    public function getRowJson(Request $req, Response $res) {
        $get = $req->getQueryParams();
        $rs = $this->no()->power()->where(array('id' => $get['id']))->fetch();
        $data = $rs->toArray();
        $return = array(
            'status' => true,
            'data' => $data,
        );
        return $res->withHeader('Content-type', 'application/json')->write(json_encode($return));
    }

    public function addPost(Request $req, Response $res) {
        $post = $req->getParsedBody();
        $post['ctime'] = time();
        $post['agent_id'] = 0;
        if ($post['id']) {
            $integral = $this->no()->power[$post['id']];
            $rs = $integral->update($post);
            if ($rs) {
                $data = ['status' => true, 'message' => '编辑成功'];
            } else {
                $data = ['status' => false, 'message' => '编辑失败'];
            }
        } else {
            $post['id'] = 0;
            $rs = $this->no()->power()->insert($post);
            if ($rs) {
                $data = ['status' => true, 'message' => '添加成功'];
            } else {
                $data = ['status' => false, 'message' => '添加失败'];
            }
        }
        return $res->withHeader('Content-type', 'application/json')->write(json_encode($data));
    }

    public function delPost(Request $req, Response $res) {
        $post = $req->getParsedBody();
        if ($post['id']) {
            $power = $this->no()->power[$post['id']];
            $post['status'] = 1;
            $rs = $power->update($post);
            if ($rs) {
                $data = ['status' => true, 'message' => '删除成功'];
            } else {
                $data = ['status' => false, 'message' => '删除失败'];
            }
        } else {
            $data = ['status' => false, 'message' => '参数错误'];
        }
        return $res->withHeader('Content-type', 'application/json')->write(json_encode($data));
    }

    

}
