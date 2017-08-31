<?php

namespace Controller\Main;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class Syslog extends Controller {

    public function actions() {
        $app = $this->app();
        $this->app()->get('/syslog.html', array($this, 'syslogGet'))->add(function (Request $req, Response $res, $next) use($app) {
            return $app->isLogin($req, $res, $next);
        })->setName('log.syslog.list');
        $this->app()->get('/syslog/list.json', array($this, 'listJson'))->add(function (Request $req, Response $res, $next) use($app) {
            return $app->isLogin($req, $res, $next);
        })->setName('log.syslog.list');
        return $this;
    }

    public function syslogGet(Request $req, Response $res) {
        $data = [
            'header' => [
                'data' => [
                    'title' => '系统日志 - 共享硬件云平台在线管理',
                ],
            ],
        ];
        $html = $this->app()->render('/Logs/syslog.html',$data);
        return $res->write($html);
    }
    
    public function listJson(Request $req, Response $res) {
        $get = $req->getQueryParams();
        $limit = 10;
        $page = isset($get['page']) ? $get['page'] : 1;
        $where = [];
        if ($get['id']) {
            $where['id'] = $get['id'];
        } else if ($get['client_id']) {
            $where['client_id'] = $get['client_id'];
        } 
        if ($get['order']) {
            $order = $get['order'];
        } else {
            $order = $get['order'] = 'id DESC';
        }
        $where['status'] = 0;
        $count = $this->no()->log_done()->select('')->where($where)->count('*');
        $num = ceil($count / $limit);
        $rs = $this->no()->log_done()->select('')->where($where)->order($order)->limit($limit, ($page - 1) * $limit);
        $data = $this->app()->iterator_array($rs);
        $return = array(
            'status' => true,
            'current' => $page,
            'total' => $num,
            'data' => $data,
        );
        return $res->withHeader('Content-type', 'application/json')->write(json_encode($return));
    }

}
