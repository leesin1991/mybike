<?php

namespace Controller\Main;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class Profile extends Controller {

    public function actions() {
        $app = $this->app();
        $this->app()->get('/profile.html', array($this, 'profileGet'))->add(function (Request $req, Response $res, $next) use($app) {
            return $app->isLogin($req, $res, $next);
        })->setName('profile');
        $this->app()->get('/profile/edit.html', array($this, 'profileeditGet'))->add(function (Request $req, Response $res, $next) use($app) {
            return $app->isLogin($req, $res, $next);
        })->setName('profile.unknown');
        $this->app()->get('/profile/list.json', array($this, 'profilelistJson'))->add(function (Request $req, Response $res, $next) use($app) {
            return $app->isLogin($req, $res, $next);
        })->setName('profile.list');
        $this->app()->get('/profile/row.json', array($this, 'getRowJson'))->add(function (Request $req, Response $res, $next) use($app) {
            return $app->isLogin($req, $res, $next);
        })->setName('profile.item');
        $this->app()->post('/profile/add.html', array($this, 'addPost'))->add(function (Request $req, Response $res, $next) use($app) {
            return $app->isLogin($req, $res, $next);
        })->setName('profile.edit');
        $this->app()->post('/profile/del.html', array($this, 'delPost'))->add(function (Request $req, Response $res, $next) use($app) {
            return $app->isLogin($req, $res, $next);
        })->setName('profile.del');
        return $this;
    }

    public function profileGet(Request $req, Response $res) {
        $data = [
            'header' => [
                'data' => [
                    'title' => '参数设置 - 共享硬件云平台在线管理',
                ],
            ],
        ];
        $html = $this->app()->render('/Profile/profile.html',$data);
        return $res->write($html);
    }
    public function profileeditGet(Request $req, Response $res) {
        $data = [
            'header' => [
                'data' => [
                    'title' => '参数编辑 - 共享硬件云平台在线管理',
                ],
            ],
        ];
        $html = $this->app()->render('/Profile/edit.html',$data);
        return $res->write($html);
    }

    public function profilelistJson(Request $req, Response $res, $args) {
        $get = $req->getQueryParams();
        $limit = 10;
        $page = isset($get['page']) ? $get['page'] : 1;
        $where = [];
        if ($get['id']) {
            $where['id'] = $get['id'];
        } else if ($get['account']) {
            $where['account'] = $get['account'];
        } else if ($get['status']) {
            $where['status'] = $get['status'];
        }
        if ($get['order']) {
            $order = $get['order'];
        } else {
            $order = $get['order'] = 'id DESC';
        }
        $where['status'] = 0;
        $count = $this->no()->profile()->select('')->where($where)->count('*');
        $num = ceil($count / $limit);
        $rs = $this->no()->profile()->select('')->where($where)->order($order)->limit($limit, ($page - 1) * $limit);
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
        $rs = $this->no()->profile()->where(array('id' => $get['id']))->fetch();
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
        if ($post['id']) {
                $class = $this->no()->profile[$post['id']];
                $rs = $class->update($post);
                if ($rs) {
                    $data = ['status' => true, 'message' => '编辑成功'];
                } else {
                    $data = ['status' => false, 'message' => '编辑失败'];
                }
        } else {
            $post['id'] = 0;
                $rs = $this->no()->profile()->insert($post);
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
            $article = $this->no()->profile[$post['id']];
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
}
