<?php

namespace Controller\Main;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class Article extends Controller {

    public function actions() {
        $app = $this->app();
        $this->app()->get('/article.html', array($this, 'articleGet'))->add(function (Request $req, Response $res, $next) use($app) {
            return $app->isLogin($req, $res, $next);
        })->setName('document.document.list');
        $this->app()->get('/article/list.json', array($this, 'listJson'))->add(function (Request $req, Response $res, $next) use($app) {
            return $app->isLogin($req, $res, $next);
        })->setName('document.document.list');
        $this->app()->get('/article/row.json', array($this, 'getRowJson'))->add(function (Request $req, Response $res, $next) use($app) {
            return $app->isLogin($req, $res, $next);
        })->setName('document.document.item');
        $this->app()->post('/article/add.html', array($this, 'addPost'))->add(function (Request $req, Response $res, $next) use($app) {
            return $app->isLogin($req, $res, $next);
        })->setName('document.document.edit');
        $this->app()->post('/article/del.html', array($this, 'delPost'))->add(function (Request $req, Response $res, $next) use($app) {
            return $app->isLogin($req, $res, $next);
        })->setName('document.document.done');
        $this->app()->get('/article/add.html', array($this, 'addGet'))->add(function (Request $req, Response $res, $next) use($app) {
            return $app->isLogin($req, $res, $next);
        })->setName('document.document.edit');
        $this->app()->get('/article/catedrop.json', array($this, 'getCateDropListJson'));
        return $this;
    }

    public function articleGet(Request $req, Response $res) {
        $data = [
            'header' => [
                'data' => [
                    'title' => '文档信息 - 共享硬件云平台在线管理',
                ],
            ],
        ];
        $html = $this->app()->render('/Article/article.html',$data);
        return $res->write($html);
    }

    public function addGet(Request $req, Response $res) {
        $data = [
            'header' => [
                'data' => [
                    'title' => '编辑文档 - 共享硬件云平台在线管理',
                ],
            ],
        ];
        $html = $this->app()->render('/Article/add.html',$data);
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
        } else if ($get['class_id']) {
            $where['class_id'] = $get['class_id'];
        } else if ($get['status']) {
            $where['status'] = $get['status'];
        }
        if ($get['order']) {
            $order = $get['order'];
        } else {
            $order = $get['order'] = 'id DESC';
        }
        $where['status'] = 0;
        $count = $this->no()->document()->select('')->where($where)->count('*');
        $num = ceil($count / $limit);
        $rs = $this->no()->document()->select('')->where($where)->order($order)->limit($limit, ($page - 1) * $limit);
        $data = $this->app()->iterator_array($rs);
        foreach ($data as $k => $v) {
            $row = $this->no()->class()->where(array('id'=>$v['class_id']))->fetch();
            $data[$k]['classname'] = $row['name'];
        }
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
        $rs = $this->no()->document()->where(array('id' => $get['id']))->fetch();
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
            $row = $this->no()->document()->where(array('name' => $post['name'], 'Not id' => $post['id']))->fetch();
            if ($row) {
                $data = ['status' => false, 'message' => '角色名称重复'];
            } else {
                $role = $this->no()->document[$post['id']];
                $rs = $role->update($post);
                if ($rs) {
                    $data = ['status' => true, 'message' => '编辑成功'];
                } else {
                    $data = ['status' => false, 'message' => '编辑失败'];
                }
            }
        } else {
            $post['id'] = 0;
            $rs = $this->no()->document()->insert($post);
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
            $article = $this->no()->document[$post['id']];
            $post['status'] = 1;
            $rs = $article->update($post);
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

    public function getCateDropListJson(Request $req, Response $res) {
        $get = $req->getQueryParams();
        if ($get['id']) {
            $sid = $get['id'];
        } 
        $data = $this->getCateDropList("class",$sid);
        $return = array(
            'status' => true,
            'data' => $data,
        );
        return $res->withHeader('Content-type', 'application/json')->write(json_encode($return));
    }



}
