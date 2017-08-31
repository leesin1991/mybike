<?php

namespace Controller\Main;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class Category extends Controller {

    public function actions() {
        $app = $this->app();
        $this->app()->get('/category.html', array($this, 'categoryGet'))->add(function (Request $req, Response $res, $next) use($app) {
            return $app->isLogin($req, $res, $next);
        })->setName('document.category.list');
        $this->app()->get('/category/list.json', array($this, 'listJson'))->add(function (Request $req, Response $res, $next) use($app) {
            return $app->isLogin($req, $res, $next);
        })->setName('document.category.list');
        $this->app()->get('/category/row.json', array($this, 'getRowJson'))->add(function (Request $req, Response $res, $next) use($app) {
            return $app->isLogin($req, $res, $next);
        })->setName('document.category.item');
        $this->app()->post('/category/add.html', array($this, 'addPost'))->add(function (Request $req, Response $res, $next) use($app) {
            return $app->isLogin($req, $res, $next);
        })->setName('document.category.edit');
        $this->app()->post('/category/del.html', array($this, 'delPost'))->add(function (Request $req, Response $res, $next) use($app) {
            return $app->isLogin($req, $res, $next);
        })->setName('document.category.done');
        return $this;
    }

    public function categoryGet(Request $req, Response $res) {
        $data = [
            'header' => [
                'data' => [
                    'title' => '文档分类 - 共享硬件云平台在线管理',
                ],
            ],
        ];
        $html = $this->app()->render('/Article/category.html',$data);
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
        } else if ($get['parent_id']) {
             $where['parent_id'] = $get['parent_id'];
        }
        if ($get['order']) {
            $order = $get['order'];
        } else {
            $order = $get['order'] = 'id DESC';
        }
        $where['status'] = 0;
        $count = $this->no()->class()->select('')->where($where)->count('*');
        $num = ceil($count / $limit);
        $rs = $this->no()->class()->select('')->where($where)->order($order)->limit($limit, ($page - 1) * $limit);
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
        $rs = $this->no()->class()->where(array('id' => $get['id']))->fetch();
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
//            $row = $this->no()->class()->where(array('name' => $post['name'], 'Not id' => $post['id']))->fetch();
//            if ($row) {
//                $data = ['status' => false, 'message' => '名称重复'];
//            } else {
                $class = $this->no()->class[$post['id']];
                $rs = $class->update($post);
                if ($rs) {
                    $data = ['status' => true, 'message' => '编辑成功'];
                } else {
                    $data = ['status' => false, 'message' => '编辑失败'];
                }
//            }
        } else {
            $post['id'] = 0;
            $row = $this->no()->class()->where(array('name' => $post['name']))->fetch();
            if ($row) {
                $data = ['status' => false, 'message' => '名称重复'];
            } else {
                $rs = $this->no()->class()->insert($post);
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
            $class = $this->no()->class[$post['id']];
            $post['status'] = 1;
            $rs = $class->update($post);
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
