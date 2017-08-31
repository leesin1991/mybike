<?php

namespace Controller\Main;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class Device extends Controller {

    public function actions() {
        $app = $this->app();
        $this->app()->get('/device.html', array($this, 'deviceGet'))->add(function (Request $req, Response $res, $next) use($app) {
            return $app->isLogin($req, $res, $next);
        })->setName('device.device.list');
        $this->app()->get('/device/list.json', array($this, 'devicelistJson'))->add(function (Request $req, Response $res, $next) use($app) {
            return $app->isLogin($req, $res, $next);
        })->setName('device.device.list');
        $this->app()->get('/device/activity.html', array($this, 'activityGet'))->add(function (Request $req, Response $res, $next) use($app) {
            return $app->isLogin($req, $res, $next);
        })->setName('device.activity.list');
        $this->app()->get('/device/activitylist.json', array($this, 'activitylistJson'))->add(function (Request $req, Response $res, $next) use($app) {
            return $app->isLogin($req, $res, $next);
        })->setName('device.activity.list');
        $this->app()->get('/device/revenue.html', array($this, 'revenueGet'))->add(function (Request $req, Response $res, $next) use($app) {
            return $app->isLogin($req, $res, $next);
        })->setName('device.revenue.list');
        $this->app()->get('/device/revenuelist.json', array($this, 'revenuelistJson'))->add(function (Request $req, Response $res, $next) use($app) {
            return $app->isLogin($req, $res, $next);
        })->setName('device.revenue.list');
        $this->app()->get('/device/tracing.html', array($this, 'tracingGet'))->add(function (Request $req, Response $res, $next) use($app) {
            return $app->isLogin($req, $res, $next);
        })->setName('device.tracing.list');
        $this->app()->get('/device/tracinglist.json', array($this, 'tracinglistJson'))->add(function (Request $req, Response $res, $next) use($app) {
            return $app->isLogin($req, $res, $next);
        })->setName('device.tracing.list');
        
        return $this;
    }

    public function deviceGet(Request $req, Response $res) {
        $data = [
            'header' => [
                'data' => [
                    'title' => '设备列表 - 共享硬件云平台在线管理',
                ],
            ],
        ];
        $html = $this->app()->render('/Device/device.html',$data);
        return $res->write($html);
    }

    public function deviceAddGet(Request $req, Response $res) {
        $data = [
            'header' => [
                'data' => [
                    'title' => '实体列表 - 共享硬件云平台在线管理',
                ],
            ],
        ];
        $html = $this->app()->render('/Device/add.html',$data);
        return $res->write($html);
    }

    public function activityGet(Request $req, Response $res) {
        $data = [
            'header' => [
                'data' => [
                    'title' => '设备活动记录 - 共享硬件云平台在线管理',
                ],
            ],
        ];
        $html = $this->app()->render('/Device/activity.html',$data);
        return $res->write($html);
    }

    public function revenueGet(Request $req, Response $res) {
        $data = [
            'header' => [
                'data' => [
                    'title' => '设备收益记录 - 共享硬件云平台在线管理',
                ],
            ],
        ];
        $html = $this->app()->render('/Device/revenue.html',$data);
        return $res->write($html);
    }

    public function tracingGet(Request $req, Response $res) {
        $data = [
            'header' => [
                'data' => [
                    'title' => '设备状态跟踪 - 共享硬件云平台在线管理',
                ],
            ],
        ];
        $html = $this->app()->render('/Device/tracing.html',$data);
        return $res->write($html);
    }
    
    //活动记录查询
    public function activitylistJson(Request $req, Response $res, $args) {
        $get = $req->getQueryParams();
        $limit = 10;
        $page = isset($get['page']) ? $get['page'] : 1;
        $where = [];
        if ($get['id']) {
            $where['id'] = $get['id'];
        } else if ($get['device_id']) {
            $where['device_id'] = $get['device_id'];
        } else if ($get['client_id']) {
            $where['client_id'] = $get['client_id'];
        }
        if ($get['order']) {
            $order = $get['order'];
        } else {
            $order = $get['order'] = 'id DESC';
        }
        $where['status'] = 0;
        $count = $this->no()->activity()->select('')->where($where)->count('*');
        $num = ceil($count / $limit);
        $rs = $this->no()->activity()->select('')->where($where)->order($order)->limit($limit, ($page - 1) * $limit);
        $data = $this->app()->iterator_array($rs);
        $return = array(
            'status' => true,
            'current' => $page,
            'total' => $num,
            'data' => $data,
        );
        return $res->withHeader('Content-type', 'application/json')->write(json_encode($return));
    }
    public function activityrowJson(Request $req, Response $res, $args) {
        $get = $req->getQueryParams();
        $rs = $this->no()->activity()->where(array('id' => $get['id']))->fetch();
        $data = $rs->toArray();
        $return = array(
            'status' => true,
            'data' => $data,
        );
        return $res->withHeader('Content-type', 'application/json')->write(json_encode($return));
    }
    
    //设备活动记录查询
    public function tracinglistJson(Request $req, Response $res, $args) {
        $get = $req->getQueryParams();
        $limit = 10;
        $page = isset($get['page']) ? $get['page'] : 1;
        $where = [];
        if ($get['id']) {
            $where['id'] = $get['id'];
        } else if ($get['device_id']) {
            $where['device_id'] = $get['device_id'];
        } else if ($get['client_id']) {
            $where['agent_id'] = $get['client_id'];
        }
        if ($get['order']) {
            $order = $get['order'];
        } else {
            $order = $get['order'] = 'id DESC';
        }
        $where['status'] = 0;
        $count = $this->no()->tracing()->select('')->where($where)->count('*');
        $num = ceil($count / $limit);
        $rs = $this->no()->tracing()->select('')->where($where)->order($order)->limit($limit, ($page - 1) * $limit);
        $data = $this->app()->iterator_array($rs);
        $return = array(
            'status' => true,
            'current' => $page,
            'total' => $num,
            'data' => $data,
        );
        return $res->withHeader('Content-type', 'application/json')->write(json_encode($return));
    }
    public function tracingrowJson(Request $req, Response $res, $args) {
        $get = $req->getQueryParams();
        $rs = $this->no()->tracing()->where(array('id' => $get['id']))->fetch();
        $data = $rs->toArray();
        $return = array(
            'status' => true,
            'data' => $data,
        );
        return $res->withHeader('Content-type', 'application/json')->write(json_encode($return));
    }
    
    //设备收益记录查询
    public function revenuelistJson(Request $req, Response $res, $args) {
        $get = $req->getQueryParams();
        $limit = 10;
        $page = isset($get['page']) ? $get['page'] : 1;
        $where = [];
        if ($get['id']) {
            $where['id'] = $get['id'];
        } else if ($get['device_id']) {
            $where['device_id'] = $get['device_id'];
        } else if ($get['client_id']) {
            $where['client_id'] = $get['client_id'];
        }
        if ($get['order']) {
            $order = $get['order'];
        } else {
            $order = $get['order'] = 'id DESC';
        }
        $where['status'] = 0;
        $count = $this->no()->revenue()->select('')->where($where)->count('*');
        $num = ceil($count / $limit);
        $rs = $this->no()->revenue()->select('')->where($where)->order($order)->limit($limit, ($page - 1) * $limit);
        $data = $this->app()->iterator_array($rs);
        $return = array(
            'status' => true,
            'current' => $page,
            'total' => $num,
            'data' => $data,
        );
        return $res->withHeader('Content-type', 'application/json')->write(json_encode($return));
    }
    public function revenuerowJson(Request $req, Response $res, $args) {
        $get = $req->getQueryParams();
        $rs = $this->no()->revenue()->where(array('id' => $get['id']))->fetch();
        $data = $rs->toArray();
        $return = array(
            'status' => true,
            'data' => $data,
        );
        return $res->withHeader('Content-type', 'application/json')->write(json_encode($return));
    }
    
    //设备列表
    public function devicelistJson(Request $req, Response $res, $args) {
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
        $count = $this->no()->device()->select('')->where($where)->count('*');
        $num = ceil($count / $limit);
        $rs = $this->no()->device()->select('')->where($where)->order($order)->limit($limit, ($page - 1) * $limit);
        $data = $this->app()->iterator_array($rs);
        $return = array(
            'status' => true,
            'current' => $page,
            'total' => $num,
            'data' => $data,
        );
        return $res->withHeader('Content-type', 'application/json')->write(json_encode($return));
    }
    public function devicerowJson(Request $req, Response $res, $args) {
        $get = $req->getQueryParams();
        $rs = $this->no()->device()->where(array('id' => $get['id']))->fetch();
        $data = $rs->toArray();
        $return = array(
            'status' => true,
            'data' => $data,
        );
        return $res->withHeader('Content-type', 'application/json')->write(json_encode($return));
    }

}
