<?php

namespace Controller\Main;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class Balance extends Controller {

    public function actions() {
        $app = $this->app();
        $this->app()->get('/balance.html', array($this, 'balanceGet'))->add(function (Request $req, Response $res, $next) use($app) {
            return $app->isLogin($req, $res, $next);
        })->setName('client.balance.list');
        $this->app()->get('/balance/list.json', array($this, 'listJson'))->add(function (Request $req, Response $res, $next) use($app) {
            return $app->isLogin($req, $res, $next);
        })->setName('client.balance.list');
        $this->app()->get('/balance/info.json', array($this, 'getInfoJson'))->add(function (Request $req, Response $res, $next) use($app) {
            return $app->isLogin($req, $res, $next);
        })->setName('client.balance.item');
        $this->app()->post('/balance/add.html', array($this, 'addPost'))->add(function (Request $req, Response $res, $next) use($app) {
            return $app->isLogin($req, $res, $next);
        })->setName('client.balance.edit');
        $this->app()->post('/balance/del.html', array($this, 'delBalance'))->add(function (Request $req, Response $res, $next) use($app) {
            return $app->isLogin($req, $res, $next);
        })->setName('client.balance.done');
        return $this;
    }

    public function balanceGet(Request $req, Response $res) {
        $data = [
            'header' => [
                'data' => [
                    'title' => '资金变动 - 共享硬件云平台在线管理',
                ],
            ],
        ];
        $html = $this->app()->render('/User/balance.html',$data);
        return $res->write($html);
    }

    public function listJson(Request $req, Response $res) {
        $get = $req->getQueryParams();
        $get['client_id'] = 0;
        $limit = 10;
        $page = isset($get['page']) ? $get['page'] : 1;
        $where = [];
        if (is_numeric($get['id'])) {
            $where['id'] = $get['id'];
        } else if ($get['client_id']) {
            $where['client_id'] = $get['client_id'];
        } else if ($get['agent_id']) {
            $where['agent_id'] = $get['agent_id'];
        } else if ($get['payment_id']) {
            $where['payment_id'] = $get['payment_id'];
        } else if ($get['status']) {
            $where['status'] = $get['status'];
        } else if ($get['mobile']) {
            $client = $this->no()->client()->where(array('mobile' => $get['mobile'], 'status' => 0))->fetch();
            $agent = $this->no()->agent()->where(array('mobile' => $get['mobile'], 'status' => 0))->fetch();
            $client_id = $client['id'];
            $agent_id = $agent['id'];
            if ($client_id) {
                $where['client_id'] = $client_id;
            }
            if ($agent_id) {
                $where['agent_id'] = $agent_id;
            }
        }
        if ($get['order']) {
            $order = $get['order'];
        } else {
            $order = $get['order'] = 'id DESC';
        }
        $where['status'] = 0;
        //print_r($where);die;
        $count = $this->no()->balance()->select('')->where($where)->count('*');
        $num = ceil($count / $limit);
        $rs = $this->no()->balance()->select('')->where($where)->order($order)->limit($limit, ($page - 1) * $limit);
        $data = $this->app()->iterator_array($rs);
        foreach ($data as &$value) {
            $value['client_name'] = $this->no()->client()->where(['id'=>$value['client_id']])->fetch()['truename'];
        }
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
        $rs = $this->no()->balance()->where(array('id' => $get['id']))->fetch();
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
        $post['agent_id'] = 1;
        $post['changed'] = 1.00;
        $post['payment_id'] = 100001;
        if ($post['id']) {
            $balance = $this->no()->balance[$post['id']];
            $rs = $balance->update($post);
            if ($rs) {
                $data = ['status' => true, 'message' => '编辑成功'];
            } else {
                $data = ['status' => false, 'message' => '编辑失败'];
            }
        } else {
            $post['id'] = 0;
            $rs = $this->no()->balance()->insert($post);
            if ($rs) {
                $data = ['status' => true, 'message' => '添加成功'];
            } else {
                $data = ['status' => false, 'message' => '添加失败'];
            }
        }
        return $res->withHeader('Content-type', 'application/json')->write(json_encode($data));
    }

    public function delBalance(Request $req, Response $res) {
        $post = $req->getParsedBody();
        if ($post['id']) {
            $balance = $this->no()->balance[$post['id']];
            $post['status'] = 1;
            $rs = $balance->update($post);
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
