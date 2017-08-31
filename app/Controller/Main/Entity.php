<?php

namespace Controller\Main;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class Entity extends Controller {

    public function actions() {
        $app = $this->app();
        //实体批次
        $this->app()->get('/entitysn.html', array($this, 'entitysnGet'))->add(function (Request $req, Response $res, $next) use($app) {
            return $app->isLogin($req, $res, $next);
        })->setName('device.entity.sn.list');
        $this->app()->get('/entitysn/list.json', array($this, 'listJson'))->add(function (Request $req, Response $res, $next) use($app) {
            return $app->isLogin($req, $res, $next);
        })->setName('device.entity.sn.list');
        $this->app()->get('/entitysn/row.json', array($this, 'getRowJson'))->add(function (Request $req, Response $res, $next) use($app) {
            return $app->isLogin($req, $res, $next);
        })->setName('device.entity.sn.item');
        $this->app()->post('/entitysn/add.html', array($this, 'addPost'))->add(function (Request $req, Response $res, $next) use($app) {
            return $app->isLogin($req, $res, $next);
        })->setName('device.entity.sn.edit');
        $this->app()->post('/entitysn/del.html', array($this, 'delPost'))->add(function (Request $req, Response $res, $next) use($app) {
            return $app->isLogin($req, $res, $next);
        })->setName('device.entity.sn.done');
        $this->app()->post('/entitysn/generate.html', array($this, 'generatePost'))->add(function (Request $req, Response $res, $next) use($app) {
            return $app->isLogin($req, $res, $next);
        })->setName('device.entity.sn.edit');
        //实体
        $this->app()->get('/entity.html', array($this, 'entityGet'))->add(function (Request $req, Response $res, $next) use($app) {
            return $app->isLogin($req, $res, $next);
        })->setName('device.entity.list');
        $this->app()->get('/entity/list.json', array($this, 'entityListJson'))->add(function (Request $req, Response $res, $next) use($app) {
            return $app->isLogin($req, $res, $next);
        })->setName('device.entity.list');
        $this->app()->get('/entity/row.json', array($this, 'getEntityRowJson'))->add(function (Request $req, Response $res, $next) use($app) {
            return $app->isLogin($req, $res, $next);
        })->setName('device.entity.item');
        //运营范围
        $this->app()->get('/entsn/range.html', array($this, 'entsnRangeGet'))->add(function (Request $req, Response $res, $next) use($app) {
            return $app->isLogin($req, $res, $next);
        })->setName('device.entity.sn.list');
        return $this;
    }

    public function entitysnGet(Request $req, Response $res) {
        $data = [
            'header' => [
                'data' => [
                    'title' => '设备批次 - 共享硬件云平台在线管理',
                ],
            ],
        ];
        $html = $this->app()->render('/Device/entitysn.html',$data);
        return $res->write($html);
    }
    
    public function entityGet(Request $req, Response $res) {
        $data = [
            'header' => [
                'data' => [
                    'title' => '实体列表 - 共享硬件云平台在线管理',
                ],
            ],
        ];
        $html = $this->app()->render('/Device/entity.html',$data);
        return $res->write($html);
    }
    
    public function entsnRangeGet(Request $req, Response $res) {
        $data = [
            'header' => [
                'data' => [
                    'title' => '运营范围管理 - 共享硬件云平台在线管理',
                ],
            ],
        ];
        $html = $this->app()->render('/Device/range.html',$data);
        return $res->write($html);
    }
    
    public function listJson(Request $req, Response $res, $args) {
        $get = $req->getQueryParams();
        $limit = 10;
        $page = isset($get['page']) ? $get['page'] : 1;
        $where = [];
        if ($get['id']) {
            $where['id'] = $get['id'];
        } else if ($get['agent_id']) {
            $where['agent_id'] = $get['agent_id'];
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
        $where['status'] = [0,2];
        $count = $this->no()->entsn()->select('')->where($where)->count('*');
        $num = ceil($count / $limit);
        $rs = $this->no()->entsn()->select('')->where($where)->order($order)->limit($limit, ($page - 1) * $limit);
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
        $rs = $this->no()->entsn()->where(array('id' => $get['id']))->fetch();
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
        $where['agent_id'] = $authed['id'];
        if ($post['id']) {
            $entsn = $this->no()->entsn[$post['id']];
            $rs = $entsn->update($post);
            if ($rs) {
                $data = ['status' => true, 'message' => '编辑成功'];
            } else {
                $data = ['status' => false, 'message' => '编辑失败'];
            }
        } else {
            $post['id'] = 0;
            $post['batch'] = $this->no()->entsn()->max('batch')+1;
            $rs = $this->no()->entsn()->insert($post);
            if ($rs) {
                $data = ['status' => true, 'message' => '添加成功'];
            } else {
                $data = ['status' => false, 'message' => '添加失败'];
            }
        }
        return $res->withHeader('Content-type', 'application/json')->write(json_encode($data));
    }

    public function generatePost(Request $req, Response $res) {
        $post = $req->getParsedBody();
        if ($post['id']) {
            $rs = $this->no()->entsn()->where(array('id' => $post['id']))->fetch();
            $row = $rs->toArray();
            $maxBatch = $this->no()->entsn()->max('batch');
            $len = strlen(floor($row['start']));
            $start = ($maxBatch+1)*pow(10, $len)+$row['start'];
            if($row['generate'] == 0){
                $data = [
                    'id' => 0,
                    'agent_id' => $row['agent_id'],
                    'entsn_id' => $row['id']
                ];
                for ($i = 0; $i < $row['total']; $i++) {
                    $data['sn'] = $start + $i;
                    $rs = $this->no()->entity()->insert($data);
                }
                if ($rs) {
                    $entsn = $this->no()->entsn[$post['id']];
                    $post['generate'] = 1;
                    $entsn->update($post);
                    $data = ['status' => true, 'message' => '生成成功'];
                } else {
                    $data = ['status' => false, 'message' => '生成失败'];
                }
            }else{
                $data = ['status' => false, 'message' => '不能重复生成'];
            }
        }else {
            $data = ['status' => false, 'message' => '参数错误'];
        }
        return $res->withHeader('Content-type', 'application/json')->write(json_encode($data));
    }

    public function delPost(Request $req, Response $res) {
        $post = $req->getParsedBody();
        if ($post['id']) {
            $entsn = $this->no()->entsn[$post['id']];
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
    
    
    public function entityListJson(Request $req, Response $res, $args) {
        $get = $req->getQueryParams();
        $limit = 10;
        $page = isset($get['page']) ? $get['page'] : 1;
        $where = [];
        if ($get['id']) {
            $where['id'] = $get['id'];
        } else if ($get['agent_id']) {
            $where['agent_id'] = $get['agent_id'];
        } else if ($get['entsn_id']) {
            $where['entsn_id'] = $get['entsn_id'];
        } else if ($get['sn']) {
            $where['sn'] = $get['sn'];
        }
        if ($get['order']) {
            $order = $get['order'];
        } else {
            $order = $get['order'] = 'id DESC';
        }
        $where['status'] = 0;
        $count = $this->no()->entity()->select('')->where($where)->count('*');
        $num = ceil($count / $limit);
        $rs = $this->no()->entity()->select('')->where($where)->order($order)->limit($limit, ($page - 1) * $limit);
        $data = $this->app()->iterator_array($rs);
        $return = array(
            'status' => true,
            'current' => $page,
            'total' => $num,
            'data' => $data,
        );
        return $res->withHeader('Content-type', 'application/json')->write(json_encode($return));
    }

    public function getEntityRowJson(Request $req, Response $res, $args) {
        $get = $req->getQueryParams();
        $rs = $this->no()->entity()->where(array('id' => $get['id']))->fetch();
        $data = $rs->toArray();
        $return = array(
            'status' => true,
            'data' => $data,
        );
        return $res->withHeader('Content-type', 'application/json')->write(json_encode($return));
    }
    
    
    
}
