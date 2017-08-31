<?php

namespace Controller\Main;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class Finance extends Controller {

    public function actions() {
        $app = $this->app();
        $this->app()->get('/deposit.html', array($this, 'depositGet'))->add(function (Request $req, Response $res, $next) use($app) {
            return $app->isLogin($req, $res, $next);
        })->setName('money.deposit.list');
        $this->app()->get('/agent_order.html', array($this, 'agentOrderGet'))->add(function (Request $req, Response $res, $next) use($app) {
            return $app->isLogin($req, $res, $next);
        })->setName('money.agent.list');
        $this->app()->get('/device_order.html', array($this, 'deviceOrderGet'))->add(function (Request $req, Response $res, $next) use($app) {
            return $app->isLogin($req, $res, $next);
        })->setName('money.device.list');
        $this->app()->get('/recharge.html', array($this, 'rechargeGet'))->add(function (Request $req, Response $res, $next) use($app) {
            return $app->isLogin($req, $res, $next);
        })->setName('money.recharge.list');
        //押金财务记录
        $this->app()->get('/deposit/list.json', array($this, 'depositListJson'))->add(function (Request $req, Response $res, $next) use($app) {
            return $app->isLogin($req, $res, $next);
        })->setName('money.deposit.list');

        //资金变动记录
        $this->app()->get('/finance/list.json', array($this, 'balanceListJson'))->add(function (Request $req, Response $res, $next) use($app) {
            return $app->isLogin($req, $res, $next);
        })->setName('money.device.list');

        return $this;
    }

    public function depositGet(Request $req, Response $res) {
        $data = [
            'header' => [
                'data' => [
                    'title' => '押金财务记录 - 共享硬件云平台在线管理',
                ],
            ],
        ];
        $html = $this->app()->render('/Finance/deposit.html',$data);
        return $res->write($html);
    }

    public function agentOrderGet(Request $req, Response $res) {
        $data = [
            'header' => [
                'data' => [
                    'title' => '代理商财务记录 - 共享硬件云平台在线管理',
                ],
            ],
        ];
        $html = $this->app()->render('/Finance/agent_order.html',$data);
        return $res->write($html);
    }

    public function deviceOrderGet(Request $req, Response $res) {
        $data = [
            'header' => [
                'data' => [
                    'title' => '购买设备记录 - 共享硬件云平台在线管理',
                ],
            ],
        ];
        $html = $this->app()->render('/Finance/device_order.html',$data);
        return $res->write($html);
    }

    public function rechargeGet(Request $req, Response $res) {
        $data = [
            'header' => [
                'data' => [
                    'title' => '用户充值记录 - 共享硬件云平台在线管理',
                ],
            ],
        ];
        $html = $this->app()->render('/Finance/recharge.html',$data);
        return $res->write($html);
    }

    /*
     * 押金
     */

    public function depositListJson(Request $req, Response $res) {
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
        }
        if ($get['order']) {
            $order = $get['order'];
        } else {
            $order = $get['order'] = 'id';
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

    /*
     * 资金记录列表
     */

    public function balanceListJson(Request $req, Response $res) {
        $get = $req->getQueryParams();
        $limit = 10;
        $page = isset($get['page']) ? $get['page'] : 1;
        $where = [];
        if (is_numeric($get['id'])) {
            $where['id'] = $get['id'];
        } else if ($get['client_id']) {
            $where['client_id'] = $get['client_id'];
        } else if ($get['payment_id']) {
            $where['payment_id'] = $get['payment_id'];
        } else if ($get['cases']) {
            $temArr = explode(',', $get['cases']);
            $where['cases'] = $temArr;
        }
        if ($get['agent_id']) {
            $where['agent_id'] = $get['agent_id'];
        }
        if ($get['order']) {
            $order = $get['order'];
        } else {
            $order = $get['order'] = 'id DESC';
        }
        $where['status'] = 0;
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

}
