<?php

namespace Controller\Main;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class Role extends Controller {

    public function actions() {
        $app = $this->app();
        $this->app()->get('/role.html', array($this, 'roleGet'))->add(function (Request $req, Response $res, $next) use($app) {
            return $app->isLogin($req, $res, $next);
        })->setName('role.role.list');
        $this->app()->get('/role/list.json', array($this, 'listJson'))->add(function (Request $req, Response $res, $next) use($app) {
            return $app->isLogin($req, $res, $next);
        })->setName('role.role.list');
        $this->app()->get('/role/row.json', array($this, 'getRowJson'))->add(function (Request $req, Response $res, $next) use($app) {
            return $app->isLogin($req, $res, $next);
        })->setName('role.role.item');
        $this->app()->post('/role/add.html', array($this, 'addPost'))->add(function (Request $req, Response $res, $next) use($app) {
            return $app->isLogin($req, $res, $next);
        })->setName('role.role.edit');
        $this->app()->post('/role/del.html', array($this, 'delPost'))->add(function (Request $req, Response $res, $next) use($app) {
            return $app->isLogin($req, $res, $next);
        })->setName('role.role.done');
        $this->app()->get('/role/test.html', array($this, 'testGet'))->add(function (Request $req, Response $res, $next) use($app) {
            return $app->isLogin($req, $res, $next);
        });
        return $this;
    }

    public function roleGet(Request $req, Response $res) {
        $data = [
            'header' => [
                'data' => [
                    'title' => '角色列表 - 共享硬件云平台在线管理',
                ],
            ],
        ];
        $html = $this->app()->render('/Role/role.html',$data);
        return $res->write($html);
    }

    public function testGet(Request $req, Response $res) {
        $data = [
            'header' => [
                'data' => [
                    'title' => '角色测试 - 共享硬件云平台在线管理',
                ],
            ],
        ];
        $html = $this->app()->render('/Role/test.html',$data);
        return $res->write($html);
    }

    public function listJson(Request $req, Response $res, $args) {
        $get = $req->getQueryParams();
        $limit = 10;
        $page = isset($get['page']) ? $get['page'] : 1;
        $where = [];
        if ($get['id']) {
            $where['id'] = $get['id'];
        } else if ($get['agent_id']) {
            $where['agent_id'] = $get['agent_id'];
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
        $count = $this->no()->role()->select('')->where($where)->count('*');
        $num = ceil($count / $limit);
        $rs = $this->no()->role()->select('')->where($where)->order($order)->limit($limit, ($page - 1) * $limit);
        $data = $this->app()->iterator_array($rs);
        $return = array(
            'status' => true,
            'current' => $page,
            'total' => $num,
            'data' => $data,
        );
        return $res->withHeader('Content-type', 'application/json')->write(json_encode($return));
    }

    public function getRowJson(Request $req, Response $res, $args) {
        $get = $req->getQueryParams();
        $rs = $this->no()->role()->where(array('id' => $get['id']))->fetch();
        $data = $rs->toArray();
        $return = array(
            'status' => true,
            'data' => $data,
        );
        return $res->withHeader('Content-type', 'application/json')->write(json_encode($return));
    }

    public function addPost(Request $req, Response $res) {
        $post = $req->getParsedBody();
        $post['agent_id'] = 0;
        $post['ctime'] = time();
        if ($post['id']) {
            $row = $this->no()->role()->where(array('name' => $post['name'], 'Not id' => $post['id']))->fetch();
            if ($row) {
                $data = ['status' => false, 'message' => '角色名称重复'];
            } else {
                $role = $this->no()->role[$post['id']];
                $rs = $role->update($post);
                if ($rs) {
                    $data = ['status' => true, 'message' => '编辑成功'];
                } else {
                    $data = ['status' => false, 'message' => '编辑失败'];
                }
            }
        } else {
            $post['id'] = 0;
            $row = $this->no()->role()->where(array('name' => $post['name']))->fetch();
            if ($row) {
                $data = ['status' => false, 'message' => '角色名称重复'];
            } else {
                $rs = $this->no()->role()->insert($post);
                if ($rs) {
                    $data = ['status' => true, 'message' => '添加成功'];
                } else {
                    $data = ['status' => false, 'message' => '添加失败'];
                }
            }
        }
        return $res->withHeader('Content-type', 'application/json')->write(json_encode($data));
    }

    public function delPost(Request $req, Response $res) {
        $post = $req->getParsedBody();
        if ($post['id']) {
            $role = $this->no()->role[$post['id']];
            $post['status'] = 1;
            $rs = $role->update($post);
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
