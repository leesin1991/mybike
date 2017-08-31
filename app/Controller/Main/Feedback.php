<?php

namespace Controller\Main;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class Feedback extends Controller {

    public function actions() {
        $app = $this->app();
        $this->app()->get('/feedback.html', array($this, 'feedbackGet'))->add(function (Request $req, Response $res, $next) use($app) {
            return $app->isLogin($req, $res, $next);
        })->setName('feed.feedback.list');
        $this->app()->get('/feedback/list.json', array($this, 'feedlist'))->add(function (Request $req, Response $res, $next) use($app) {
            return $app->isLogin($req, $res, $next);
        })->setName('feed.feedback.list');
        return $this;
    }

    public function feedbackGet(Request $req, Response $res) {
        $data = [
            'header' => [
                'data' => [
                    'title' => '信息反馈 - 共享硬件云平台在线管理',
                ],
            ],
        ];
        $html = $this->app()->render('/Feedback/feedback.html',$data);
        return $res->write($html);
    }
    
    public function feedlist(Request $req, Response $res){
        $get = $req->getQueryParams();
        $limit = 10;
        $page = isset($get['page']) ? $get['page'] : 1;
        $where = [];
        if ($get['id']) {
            $where['id'] = $get['id'];
        } else if ($get['client_id']) {
            $where['client_id'] = $get['client_id'];
        } else if ($get['status']) {
            $where['status'] = $get['status'];
        } else if ($get['device_id']) {
            $where['device_id'] = $get['device_id'];
        } 
        if ($get['order']) {
            $order = $get['order'];
        } else {
            $order = $get['order'] = 'id DESC';
        }
        $where['status'] = 0;
        $count = $this->no()->feedback()->select('')->where($where)->count('*');
        $num = ceil($count / $limit);
        $rs = $this->no()->feedback()->select('')->where($where)->order($order)->limit($limit, ($page - 1) * $limit);
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

}
