<?php

namespace Controller\Main;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class Coupon extends Controller {

    public function actions() {
        $app = $this->app();
        $this->app()->get('/coupon.html', array($this, 'couponGet'))->add(function (Request $req, Response $res, $next) use($app) {
            return $app->isLogin($req, $res, $next);
        })->setName('coupon.coupon.list');
        $this->app()->get('/coupon/list.json', array($this, 'listJson'))->add(function (Request $req, Response $res, $next) use($app) {
            return $app->isLogin($req, $res, $next);
        })->setName('coupon.coupon.list');
        $this->app()->get('/coupon/row.json', array($this, 'getRowJson'))->add(function (Request $req, Response $res, $next) use($app) {
            return $app->isLogin($req, $res, $next);
        })->setName('coupon.coupon.item');
        $this->app()->post('/coupon/add.html', array($this, 'addPost'))->add(function (Request $req, Response $res, $next) use($app) {
            return $app->isLogin($req, $res, $next);
        })->setName('coupon.coupon.edit');
        $this->app()->post('/coupon/del.html', array($this, 'delPost'))->add(function (Request $req, Response $res, $next) use($app) {
            return $app->isLogin($req, $res, $next);
        })->setName('coupon.coupon.done');
        return $this;
    }

    public function couponGet(Request $req, Response $res) {
        $data = [
            'header' => [
                'data' => [
                    'title' => '优惠券列表 - 共享硬件云平台在线管理',
                ],
            ],
        ];
        $html = $this->app()->render('/Coupon/coupon.html',$data);
        return $res->write($html);
    }

    public function listJson(Request $req, Response $res, $args) {
        $get = $req->getQueryParams();
        $limit = 10;
        $page = isset($get['page']) ? $get['page'] : 1;
        $where = [];
        if ($get['id']) {
            $where['id'] = $get['id'];
        } else if ($get['client_id']) {
            $where['client_id'] = $get['client_id'];
        } else if ($get['cousn_id']) {
            $where['cousn_id'] = $get['cousn_id'];
        } else if ($get['sn']) {
            $where['sn'] = $get['sn'];
        }
        if ($get['order']) {
            $order = $get['order'];
        } else {
            $order = $get['order'] = 'id DESC';
        }
        $where['status'] = 0;
        $count = $this->no()->coupon()->select('')->where($where)->count('*');
        $num = ceil($count / $limit);
        $rs = $this->no()->coupon()->select('')->where($where)->order($order)->limit($limit, ($page - 1) * $limit);
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
        $rs = $this->no()->coupon()->where(array('id' => $get['id']))->fetch();
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
                $class = $this->no()->coupon[$post['id']];
                $rs = $class->update($post);
                if ($rs) {
                    $data = ['status' => true, 'message' => '编辑成功'];
                } else {
                    $data = ['status' => false, 'message' => '编辑失败'];
                }
        } else {
            $post['id'] = 0;
                $rs = $this->no()->coupon()->insert($post);
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
            $article = $this->no()->coupon[$post['id']];
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
