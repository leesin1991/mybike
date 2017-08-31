<?php

namespace Controller\Main;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class Cousn extends Controller {

    public function actions() {
        $app = $this->app();
        $this->app()->get('/cousn.html', array($this, 'cousnGet'))->add(function (Request $req, Response $res, $next) use($app) {
            return $app->isLogin($req, $res, $next);
        })->setName('coupon.coupon.sn.list');
        $this->app()->get('/cousn/list.json', array($this, 'cousnlistJson'))->add(function (Request $req, Response $res, $next) use($app) {
            return $app->isLogin($req, $res, $next);
        })->setName('coupon.coupon.sn.list');
        $this->app()->get('/cousn/row.json', array($this, 'getRowJson'))->add(function (Request $req, Response $res, $next) use($app) {
            return $app->isLogin($req, $res, $next);
        })->setName('coupon.coupon.sn.item');
        $this->app()->post('/cousn/add.html', array($this, 'addPost'))->add(function (Request $req, Response $res, $next) use($app) {
            return $app->isLogin($req, $res, $next);
        })->setName('coupon.coupon.sn.edit');
        $this->app()->post('/cousn/del.html', array($this, 'delPost'))->add(function (Request $req, Response $res, $next) use($app) {
            return $app->isLogin($req, $res, $next);
        })->setName('coupon.coupon.sn.done');
        $this->app()->post('/cousn/generate.html', array($this, 'generatePost'))->add(function (Request $req, Response $res, $next) use($app) {
            return $app->isLogin($req, $res, $next);
        })->setName('coupon.coupon.sn.edit');
        return $this;
    }

    public function cousnGet(Request $req, Response $res) {
        $data = [
            'header' => [
                'data' => [
                    'title' => '优惠券批次 - 共享硬件云平台在线管理',
                ],
            ],
        ];
        $html = $this->app()->render('/Cousn/cousn.html',$data);
        return $res->write($html);
    }

    public function cousnlistJson(Request $req, Response $res, $args) {
        $get = $req->getQueryParams();
        $limit = 10;
        $page = isset($get['page']) ? $get['page'] : 1;
        $where = [];
        if ($get['id']) {
            $where['id'] = $get['id'];
        } else if ($get['batch']) {
            $where['batch'] = $get['batch'];
        } else if ($get['name']) {
            $where['name'] = $get['name'];
        }
        if ($get['order']) {
            $order = $get['order'];
        } else {
            $order = $get['order'] = 'id DESC';
        }
        $where['status'] = 0;
        $count = $this->no()->cousn()->select('')->where($where)->count('*');
        $num = ceil($count / $limit);
        $rs = $this->no()->cousn()->select('')->where($where)->order($order)->limit($limit, ($page - 1) * $limit);
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
        $rs = $this->no()->cousn()->where(array('id' => $get['id']))->fetch();
        $data = $rs->toArray();
        $return = array(
            'status' => true,
            'data' => $data,
        );
        return $res->withHeader('Content-type', 'application/json')->write(json_encode($return));
    }

    public function addPost(Request $req, Response $res) {
        $post = $req->getParsedBody();
        $authed = $this->authed();
        if ($post['id']) {
            $post['period'] = strtotime($post['period']);
            $class = $this->no()->cousn[$post['id']];
            $rs = $class->update($post);
            if ($rs) {
                $data = ['status' => true, 'message' => '编辑成功'];
            } else {
                $data = ['status' => false, 'message' => '编辑失败'];
            }
        } else {
            $post['id'] = 0;
            $post['agent_id'] = $authed['id'];
            $post['period'] = strtotime($post['period']);
            $rs = $this->no()->cousn()->insert($post);
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
            $entsn = $this->no()->cousn[$post['id']];
            $post['status'] = 1;
            $rs = $entsn->update($post);
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

    public function generatePost(Request $req, Response $res) {
        $post = $req->getParsedBody();
        if ($post['id']) {
            $now = time();
            $rs = $this->no()->cousn()->where(array('id' => $post['id'],'status'=>0))->fetch();
            $row = $rs->toArray();
            if ($row['generate'] == 0 ) {
                if($row['period'] > $now){
                    $data = [
                        'id' => 0,
                        'agent_id' => $row['agent_id'],
                        'cousn_id' => $row['id'],
                        'start' => $now,
                        'end' => $row['period'],
                        'ctime' => $now
                    ];         
                    for ($i = 0; $i < $row['total']; $i++) {
                        $randcode = $this->getRandom(15);
                        $startsn = $row['batch'] . $randcode;
                        $data['sn'] = $startsn;
                        $rs = $this->no()->coupon()->insert($data);
                    }
                    if ($rs) {
                        $cousn = $this->no()->cousn[$post['id']];
                        $post['generate'] = 1;
                        $cousn->update($post);
                        $data = ['status' => true, 'message' => '生成成功'];
                    } else {
                        $data = ['status' => false, 'message' => '生成失败'];
                    }
                }else{
                    $data = ['status' => false, 'message' => '有效时间已过期'];
                }
            } else {
                $data = ['status' => false, 'message' => '不能重复生成'];
            }
        }else {
            $data = ['status' => false, 'message' => '参数错误'];
        }
        return $res->withHeader('Content-type', 'application/json')->write(json_encode($data));
    }

}
