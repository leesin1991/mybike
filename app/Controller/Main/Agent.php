<?php

namespace Controller\Main;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class Agent extends Controller {

    public function actions() {
        $app = $this->app();
        $this->app()->get('/agent.html', array($this, 'articleGet'))->add(function (Request $req, Response $res, $next) use($app) {
            return $app->isLogin($req, $res, $next);
        })->setName('agent.agent.list');
        $this->app()->get('/agent/list.json', array($this, 'listJson'))->add(function (Request $req, Response $res, $next) use($app) {
            return $app->isLogin($req, $res, $next);
        })->setName('agent.agent.list');
        $this->app()->get('/agent/row.json', array($this, 'getRowJson'))->add(function (Request $req, Response $res, $next) use($app) {
            return $app->isLogin($req, $res, $next);
        })->setName('agent.agent.item');
        $this->app()->post('/agent/add.html', array($this, 'addPost'))->add(function (Request $req, Response $res, $next) use($app) {
            return $app->isLogin($req, $res, $next);
        })->setName('agent.agent.edit');
        $this->app()->post('/agent/del.html', array($this, 'delPost'))->add(function (Request $req, Response $res, $next) use($app) {
            return $app->isLogin($req, $res, $next);
        })->setName('agent.agent.done');
        $this->app()->get('/agent/catedrop.json', array($this, 'getCateDropListJson'));
        return $this;
    }

    public function articleGet(Request $req, Response $res) {
        $data = [
            'header' => [
                'data' => [
                    'title' => '代理管理 - 共享硬件云平台在线管理',
                ],
            ],
        ];
        $html = $this->app()->render('/Agent/agent.html', $data);
        return $res->write($html);
    }

    public function listJson(Request $req, Response $res, $args) {
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
        $count = $this->no()->agent()->select('')->where($where)->count('*');
        $num = ceil($count / $limit);
        $rs = $this->no()->agent()->select('')->where($where)->order($order)->limit($limit, ($page - 1) * $limit);
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
        $rs = $this->no()->agent()->where(array('id' => $get['id']))->fetch();
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
//            $row = $this->no()->agent()->where(array('account' => $post['account'], 'mobile' => $post['mobile'],'email' => $post['email'], 'Not id' => $post['id']))->fetch();
            $row1 = $this->no()->agent()->where(array('account' => $post['account']))->fetch();
            $row2 = $this->no()->agent()->where(array('mobile' => $post['mobile']))->fetch();
            $row3 = $this->no()->agent()->where(array('email' => $post['email']))->fetch();
            if ($row1) {
                $data = ['status' => false, 'message' => '代理商账户重复'];
            } else if ($row2) {
                $data = ['status' => false, 'message' => '联系电话重复'];
            } else if ($row3) {
                $data = ['status' => false, 'message' => '邮箱重复'];
            } else {
                $role = $this->no()->agent[$post['id']];
                $rs = $role->update($post);
                if ($rs) {
                    $data = ['status' => true, 'message' => '编辑成功'];
                } else {
                    $data = ['status' => false, 'message' => '编辑失败'];
                }
            }
        } else {

            $post['id'] = 0;

            $row1 = $this->no()->agent()->where(array('account' => $post['account']))->fetch();
            $row2 = $this->no()->agent()->where(array('mobile' => $post['mobile']))->fetch();
            $row3 = $this->no()->agent()->where(array('email' => $post['email']))->fetch();
            if ($row1) {
                $data = ['status' => false, 'message' => '代理商账户重复'];
            } else if ($row2) {
                $data = ['status' => false, 'message' => '联系电话重复'];
            } else if ($row3) {
                $data = ['status' => false, 'message' => '邮箱重复'];
            } else {
                $rs = $this->no()->agent()->insert($post);
                if ($rs) {
                    $data = ['status' => true, 'message' => '添加成功'];
                } else {
                    $data = ['status' => false, 'message' => "添加失败"];
                }
            }
        }
        return $res->withHeader('Content-type', 'application/json')->write(json_encode($data));
    }

    public function delPost(Request $req, Response $res) {
        $post = $req->getParsedBody();
        if ($post['id']) {
            $article = $this->no()->agent[$post['id']];
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
        $data = $this->getCateDropList("agent", $sid);
        $return = array(
            'status' => true,
            'data' => $data,
        );
        return $res->withHeader('Content-type', 'application/json')->write(json_encode($return));
    }

}
