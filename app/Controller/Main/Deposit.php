<?php

namespace Controller\Main;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class Deposit extends Controller {

    public function actions() {
        $app = $this->app();
        $this->app()->get('/deposit.html', array($this, 'depositGet'))->add(function (Request $req, Response $res, $next) use($app) {
            return $app->isLogin($req, $res, $next);
        });
        $this->app()->get('/deposit/list.json', array($this, 'listJson'))->add(function (Request $req, Response $res, $next) use($app) {
            return $app->isLogin($req, $res, $next);
        });
        $this->app()->get('/deposit/row.json', array($this, 'getRowJson'))->add(function (Request $req, Response $res, $next) use($app) {
            return $app->isLogin($req, $res, $next);
        });
        return $this;
    }

    public function depositGet(Request $req, Response $res) {
        $html = $this->app()->render('/User/deposit.html');
        return $res->write($html);
    }

    public function listJson(Request $req, Response $res) {
        $get = $req->getQueryParams();
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
            $order = 'id DESC';
        }
        $where['status'] = 0;
        $count = $this->no()->deposit()->select('')->where($where)->count('*');
        $num = ceil($count / $limit);
        $rs = $this->no()->deposit()->select('')->where($where)->order($order)->limit($limit, ($page - 1) * $limit);
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
        $rs = $this->no()->deposit()->where(array('id' => $get['id']))->fetch();
        $data = $rs->toArray();
        $return = array(
            'status' => true,
            'data' => $data,
        );
        return $res->withHeader('Content-type', 'application/json')->write(json_encode($return));
    }

}
