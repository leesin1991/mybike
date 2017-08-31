<?php

namespace Controller\Main;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class Message extends Controller {

    public function actions() {
        $app = $this->app();
        
        $this->app()->get('/message.html', array($this, 'messageGet'))->add(function (Request $req, Response $res, $next) use($app) {
            return $app->isLogin($req, $res, $next);
        })->setName('message.message.list');
        $this->app()->get('/message/list.json', array($this, 'messagelistJson'))->add(function (Request $req, Response $res, $next) use($app) {
            return $app->isLogin($req, $res, $next);
        })->setName('message.message.list');
        $this->app()->get('/message/row.json', array($this, 'messageRowJson'))->add(function (Request $req, Response $res, $next) use($app) {
            return $app->isLogin($req, $res, $next);
        })->setName('message.message.item');
        return $this;
    }

    public function messageGet(Request $req, Response $res) {
        $data = [
            'header' => [
                'data' => [
                    'title' => '系统信息 - 共享硬件云平台在线管理',
                ],
            ],
        ];
        $html = $this->app()->render('/Message/message.html',$data);
        return $res->write($html);
    }


    public function messagelistJson(Request $req, Response $res) {
        $get = $req->getQueryParams();
        $limit = 10;
        $page = isset($get['page']) ? $get['page'] : 1;
        $where = [];
        if ($get['id']) {
            $where['id'] = $get['id'];
        } else if ($get['agent_id']) {
            $where['agent_id'] = $get['agent_id'];
        }
        if ($get['order']) {
            $order = $get['order'];
        } else {
            $order = $get['order'] = 'id DESC';
        }
        $where['status'] = 0;
        $count = $this->no()->message()->select('')->where($where)->count('*');
        $num = ceil($count / $limit);
        $rs = $this->no()->message()->select('')->where($where)->order($order)->limit($limit, ($page - 1) * $limit);
        $data = $this->app()->iterator_array($rs);
        $return = array(
            'status' => true,
            'current' => $page,
            'total' => $num,
            'data' => $data,
        );
        return $res->withHeader('Content-type', 'application/json')->write(json_encode($return));
    }

    public function messageRowJson(Request $req, Response $res) {
        $get = $req->getQueryParams();
        $rs = $this->no()->message()->where(array('id' => $get['id']))->fetch();
        $data = $rs->toArray();
        $return = array(
            'status' => true,
            'data' => $data,
        );
        return $res->withHeader('Content-type', 'application/json')->write(json_encode($return));
    }
    


}
