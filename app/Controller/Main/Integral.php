<?php

namespace Controller\Main;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class Integral extends Controller {

    public function actions() {
        $app = $this->app();
        $this->app()->get('/integral.html', array($this, 'integralGet'))->add(function (Request $req, Response $res, $next) use($app) {
            return $app->isLogin($req, $res, $next);
        })->setName('client.integral.list');
        $this->app()->get('/integral/list.json', array($this, 'listJson'))->add(function (Request $req, Response $res, $next) use($app) {
            return $app->isLogin($req, $res, $next);
        })->setName('client.integral.list');
        $this->app()->get('/integral/info.json', array($this, 'getInfoJson'))->add(function (Request $req, Response $res, $next) use($app) {
            return $app->isLogin($req, $res, $next);
        })->setName('client.integral.item');
        $this->app()->post('/integral/add.html', array($this, 'addPost'))->add(function (Request $req, Response $res, $next) use($app) {
            return $app->isLogin($req, $res, $next);
        })->setName('client.integral.edit');
        $this->app()->post('/integral/del.html', array($this, 'delIntegral'))->add(function (Request $req, Response $res, $next) use($app) {
            return $app->isLogin($req, $res, $next);
        })->setName('client.integral.done');
        return $this;
    }

    public function integralGet(Request $req, Response $res, $args) {
        $data = [
            'header' => [
                'data' => [
                    'title' => '积分记录 - 共享硬件云平台在线管理',
                ],
            ],
        ];
        $html = $this->app()->render('/User/integral.html',$data);
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
        } else if ($get['agent_id']) {
            $where['agent_id'] = $get['agent_id'];
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
        if ($get['current']) {
            $where['current'] = $get['current'];
        }
        if ($get['order']) {
            $order = $get['order'];
        } else {
            $order = $get['order'] = 'id DESC';
        }
        $where['status'] = 0;
        $count = $this->no()->integral()->select('')->where($where)->count('*');
        $num = ceil($count / $limit);
        $rs = $this->no()->integral()->select('')->where($where)->order($order)->limit($limit, ($page - 1) * $limit);
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
        $rs = $this->no()->integral()->where(array('id' => $get['id']))->fetch();
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
        if ($post['id']) {
            $integral = $this->no()->integral[$post['id']];
            $rs = $integral->update($post);
            if ($rs) {
                $data = ['status' => true, 'message' => '编辑成功'];
            } else {
                $data = ['status' => false, 'message' => '编辑失败'];
            }
        } else {
            $post['id'] = 0;
            $rs = $this->no()->integral()->insert($post);
            if ($rs) {
                $data = ['status' => true, 'message' => '添加用户成功'];
            } else {
                $data = ['status' => false, 'message' => '添加失败'];
            }
        }
        return $res->withHeader('Content-type', 'application/json')->write(json_encode($data));
    }

    public function delIntegral(Request $req, Response $res) {
        $post = $req->getParsedBody();
        if ($post['id']) {
            $integral = $this->no()->integral[$post['id']];
            $post['status'] = 1;
            $rs = $integral->update($post);
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
