<?php

namespace Controller\Main;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class User extends Controller {

    public function actions() {
        $app = $this->app();
        $this->app()->get('/user.html', array($this, 'userGet'))->add(function (Request $req, Response $res, $next) use($app) {
            return $app->isLogin($req, $res, $next);
        })->setName('client.client.list');
        $this->app()->get('/user/list.json', array($this, 'userListJson'))->add(function (Request $req, Response $res, $next) use($app) {
            return $app->isLogin($req, $res, $next);
        })->setName('client.client.list');
        $this->app()->get('/user/info.json', array($this, 'getInfoJson'))->add(function (Request $req, Response $res, $next) use($app) {
            return $app->isLogin($req, $res, $next);
        })->setName('client.client.item');
        $this->app()->post('/user/add.html', array($this, 'addPost'))->add(function (Request $req, Response $res, $next) use($app) {
            return $app->isLogin($req, $res, $next);
        })->setName('client.client.edit');
        $this->app()->post('/user/del.html', array($this, 'delUser'))->add(function (Request $req, Response $res, $next) use($app) {
            return $app->isLogin($req, $res, $next);
        })->setName('client.client.done');
        return $this;
    }

    public function userGet(Request $req, Response $res, $args) {
        $data = [
            'header' => [
                'data' => [
                    'title' => '用户列表 - 共享硬件云平台在线管理',
                ],
            ],
        ];
        $html = $this->app()->render('/User/user.html',$data);
        return $res->write($html);
    }

    public function userListJson(Request $req, Response $res, $args) {
        $get = $req->getQueryParams();
        $limit = 10;
        $page = isset($get['page']) ? $get['page'] : 1;
        $where = [];
        if ($get['id']) {
            $where['id'] = $get['id'];
        } else if ($get['mobile']) {
            $where['mobile'] = $get['mobile'];
        } else if ($get['credit_point']) {
            $where['integral'] = $get['credit_point'];
        } else if ($get['status']) {
            $where['status'] = $get['status'];
        }
        if ($get['order']) {
            $order = $get['order'];
        } else {
            $order = $get['order'] = 'id DESC';
        }
        $where['status'] = 0;
        $count = $this->no()->client()->select('')->where($where)->count('*');
        $num = ceil($count / $limit);
        $rs = $this->no()->client()->select('')->where($where)->order($order)->limit($limit, ($page - 1) * $limit);
        $data = $this->app()->iterator_array($rs);
        $return = array(
            'status' => true,
            'current' => $page,
            'total' => $num,
            'data' => $data,
        );
        return $res->withHeader('Content-type', 'application/json')->write(json_encode($return));
    }

    public function getInfoJson(Request $req, Response $res, $args) {
        $get = $req->getQueryParams();
        $rs = $this->no()->client()->where(array('id' => $get['id']))->fetch();
        $data = $rs->toArray();
        $return = array(
            'status' => true,
            'data' => $data,
        );
        return $res->withHeader('Content-type', 'application/json')->write(json_encode($return));
    }

    public function addPost(Request $req, Response $res) {
        $post = $req->getParsedBody();
        $post['mtime'] = $post['ctime'] = time();
        $post['passwd'] = md5(123456);
        $post['agent_id'] = 0;
        if ($post['id']) {
            $client = $this->no()->client[$post['id']];
            $rs = $client->update($post);
            if ($rs) {
                $data = ['status' => true, 'message' => '编辑成功'];
            } else {
                $data = ['status' => false, 'message' => '编辑失败'];
            }
        } else {
            $row = $this->no()->client()->where(array('mobile' => $post['mobile']))->fetch();
            if ($row) {
                $data = ['status' => false, 'message' => '手机号已存在'];
            } else {
                $post['id'] = 0;
                $rs = $this->no()->client()->insert($post);
                if ($rs) {
                    $data = ['status' => true, 'message' => '添加用户成功'];
                } else {
                    $data = ['status' => false, 'message' => '添加失败'];
                }
            }
        }
        return $res->withHeader('Content-type', 'application/json')->write(json_encode($data));
    }

    public function delUser(Request $req, Response $res, $args) {
        $post = $req->getParsedBody();
        if ($post['id']) {
            $post['status'] = 1;
            $client = $this->no()->client[$post['id']];
            $rs = $client->update($post);
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
