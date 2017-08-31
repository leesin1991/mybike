<?php

namespace Controller\Main;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class Revenue extends Controller {

    public function actions() {
        $app = $this->app();
        $this->app()->get('/revenue/day.html', array($this, 'revenueDayGet'))->add(function (Request $req, Response $res, $next) use($app) {
            return $app->isLogin($req, $res, $next);
        })->setName('agent.revenue.day.list');
        $this->app()->get('/revenue/month.html', array($this, 'revenueMonthGet'))->add(function (Request $req, Response $res, $next) use($app) {
            return $app->isLogin($req, $res, $next);
        })->setName('agent.revenue.month.list');
        $this->app()->get('/revenue/revenue.html', array($this, 'revenueGet'))->add(function (Request $req, Response $res, $next) use($app) {
            return $app->isLogin($req, $res, $next);
        })->setName('revenue.revenue');
        $this->app()->get('/revenue/list.json', array($this, 'listJson'))->add(function (Request $req, Response $res, $next) use($app) {
            return $app->isLogin($req, $res, $next);
        })->setName('agent.revenue.day.list');
        $this->app()->get('/revenue/row.json', array($this, 'getRowJson'))->add(function (Request $req, Response $res, $next) use($app) {
            return $app->isLogin($req, $res, $next);
        })->setName('agent.revenue.day.item');
        $this->app()->get('/revenue/day.json', array($this, 'searchDayProfitJson'))->add(function (Request $req, Response $res, $next) use($app) {
            return $app->isLogin($req, $res, $next);
        })->setName('agent.revenue.day.list');
        $this->app()->get('/revenue/month.json', array($this, 'searchMonthProfitJson'))->add(function (Request $req, Response $res, $next) use($app) {
            return $app->isLogin($req, $res, $next);
        })->setName('agent.revenue.month.list');
        $this->app()->get('/revenue/crontab.html', array($this, 'revenueCrontab'));
        return $this;
    }

    public function revenueDayGet(Request $req, Response $res) {
        $data = [
            'header' => [
                'data' => [
                    'title' => '代理日收益 - 共享硬件云平台在线管理',
                ],
            ],
        ];
        $html = $this->app()->render('/Revenue/day.html',$data);
        return $res->write($html);
    }

    public function revenueMonthGet(Request $req, Response $res) {
        $data = [
            'header' => [
                'data' => [
                    'title' => '代理月收益 - 共享硬件云平台在线管理',
                ],
            ],
        ];
        $html = $this->app()->render('/Revenue/month.html',$data);
        return $res->write($html);
    }

    public function revenueGet(Request $req, Response $res) {
        $data = [
            'header' => [
                'data' => [
                    'title' => '代理日收益 - 共享硬件云平台在线管理',
                ],
            ],
        ];
        $html = $this->app()->render('/Revenue/revenue.html',$data);
        return $res->write($html);
    }

    public function searchDayProfitJson(Request $req, Response $res) {
        $get = $req->getQueryParams();
        $limit = 10;
        $page = isset($get['page']) ? $get['page'] : 1;
        $authed = $this->authed();
        $where['agent_id'] = $authed['id'];
        if ($get['agent_id']) {
            $where['agent_id'] = $get['agent_id'];
        } else if ($get['date']) {
            $where['day'] = date("Ymd", strtotime($get['date']));
        }
        $where['status'] = 0;
        $order = isset($get['order']) ? $get['order'] : "id";
        $count = $this->no()->revenue_day()->select('')->where($where)->count('*');
        $num = ceil($count / $limit);
        $rs = $this->no()->revenue_day()->select('')->where($where)->order($order)->limit($limit, ($page - 1) * $limit);
        $data = $this->app()->iterator_array($rs);
        $return = array(
            'status' => true,
            'current' => $page,
            'total' => $num,
            'data' => $data,
        );
        return $res->withHeader('Content-type', 'application/json')->write(json_encode($return));
    }

    public function searchMonthProfitJson(Request $req, Response $res) {
        $get = $req->getQueryParams();
        $limit = 10;
        $page = isset($get['page']) ? $get['page'] : 1;
        $authed = $this->authed();
        $where['agent_id'] = $authed['id'];
        if ($get['agent_id']) {
            $where['agent_id'] = $get['agent_id'];
        } else if ($get['date']) {
            $where['month'] = date("Ym", strtotime($get['date']));
        }
        $where['status'] = 0;
        $order = isset($get['order']) ? $get['order'] : "id DESC";
        $count = $this->no()->revenue_month()->select('')->where($where)->count('*');
        $num = ceil($count / $limit);
        $rs = $this->no()->revenue_month()->select('')->where($where)->order($order)->limit($limit, ($page - 1) * $limit);
        $data = $this->app()->iterator_array($rs);
        $return = array(
            'status' => true,
            'current' => $page,
            'total' => $num,
            'data' => $data,
        );
        return $res->withHeader('Content-type', 'application/json')->write(json_encode($return));
    }

    public function listJson(Request $req, Response $res) {
        $get = $req->getQueryParams();
        $limit = 10;
        $page = isset($get['page']) ? $get['page'] : 1;
        $authed = $this->authed();
        $where['agent_id'] = $authed['id'];
        if ($get['id']) {
            $where['id'] = $get['id'];
        } else if ($get['client_id']) {
            $where['client_id'] = $get['client_id'];
        } else if ($get['device_id']) {
            $where['device_id'] = $get['device_id'];
        }
        if ($get['day']) {
            $where["FROM_UNIXTIME(ctime,'%Y-%m-%d')"] = $get['day'];
        } else if ($get['month']) {
            $where["FROM_UNIXTIME(ctime,'%Y-%m')"] = $get['month'];
        }
        $where['status'] = 0;
        $order = isset($get['order']) ? $get['order'] : "id DESC";
        $count = $this->no()->revenue()->select("")->where($where)->count('*');
        $num = ceil($count / $limit);
        $rs = $this->no()->revenue()->select("")->where($where)->order($order)->limit($limit, ($page - 1) * $limit);
        $data = $this->app()->iterator_array($rs);
        $return = array(
            'status' => true,
            'current' => $page,
            'total' => $num,
            'data' => $data,
        );
        return $res->withHeader('Content-type', 'application/json')->write(json_encode($return));
    }

    public function getRowJson(Request $req, Response $res) {
        $get = $req->getQueryParams();
        $authed = $this->authed();
        $where = [
            'id' => $get['id'],
            'agent_id' => $authed['id']
        ];
        $rs = $this->no()->revenue()->where($where)->fetch();
        $data = $rs->toArray();
        $return = array(
            'status' => true,
            'data' => $data,
        );
        return $res->withHeader('Content-type', 'application/json')->write(json_encode($return));
    }

    /*
     * 更新每日收益
     * 
     */

    public function revenueCrontab(Request $req, Response $res) {
        $now = time();
        $rs = $this->no()->agent()->select('id');
        $list = $this->app()->iterator_array($rs);
        foreach ($list as $key => $value) {
            $agentArr[] = $value['id'];
        }
        for ($i = 0; $i < count($agentArr); $i++) {
            $where = [];
            $where['agent_id'] = $agentArr[$i];
            $dayResult = $this->no()->revenue()->select("FROM_UNIXTIME(ctime,'%Y%m%d') AS day,SUM(profit) AS total")->where($where)->group(day)->order('ctime desc')->limit(1);
            $dayData = $this->app()->iterator_array($dayResult);
            $monthResult = $this->no()->revenue()->select("FROM_UNIXTIME(ctime,'%Y%m') AS month,SUM(profit) AS total")->where($where)->group(month)->order('ctime desc')->limit(1);
            $monthData = $this->app()->iterator_array($monthResult);
            $today = $dayData[0]["day"];
            $month = $monthData[0]["month"];
            $daydata = [
                "agent_id" => $agentArr[$i],
                "ctime" => $now,
                "day" => $today,
                "profit" => $dayData[0]["total"]
            ];
            $monthdata = [
                "agent_id" => $agentArr[$i],
                "ctime" => $now,
                "month" => $month,
                "profit" => $monthData[0]["total"]
            ];
            $dayRow = $this->no()->revenue_day()->where(array("day" => $today, "agent_id" => $agentArr[$i]))->fetch();
            $monthRow = $this->no()->revenue_month()->where(array("month" => $month, "agent_id" => $agentArr[$i]))->fetch();
            if ($dayRow) {
                $daydata['id'] = $dayRow['id'];
                $revenue_day = $this->no()->revenue_day[$daydata['id']];
                $day_rs = $revenue_day->update($daydata);
            } else {
                $daydata['id'] = 0;
                $day_rs = $this->no()->revenue_day()->insert($daydata);
            }
            if ($monthRow) {
                $monthdata['id'] = $monthRow['id'];
                $revenue_month = $this->no()->revenue_month[$monthdata['id']];
                $month_rs = $revenue_month->update($monthdata);
            } else {
                $monthdata['id'] = 0;
                $month_rs = $this->no()->revenue_month()->insert($monthdata);
            }
        }
    }

}
