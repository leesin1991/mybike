<?php

namespace Controller\Main;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Util\Alipay\Server;

class Test extends Controller {

    public function actions() {
        $this->app()->get('/test/register.html', array($this, 'registerGet'));
        $this->app()->get('/test/sms.html', array($this, 'smsGet'));
        $this->app()->get('/test/alipay.html', array($this, 'alipay'));
        $this->app()->post('/notify.html', array($this, 'notify'));
        $this->app()->get('/test/unlock.html', array($this, 'unlock'));
        return $this;
    }
    
    public function unlock(Request $req, Response $res, $args) {
        
        $this->app()->cache()->rpush('waitting_unlock',  json_encode(array(
            'uid' => 1,
            'did' => 1,
        )));
        return $res->write(597869e1);
    }

    public function registerGet(Request $req, Response $res, $args) {
        //print_r(md5('123456789'));die;
        $app_id = substr(md5(uniqid()), 0, 15);
        $regform = [
            'imei' => $app_id,
            'code' => md5($app_id),
        ];
        $regjson = $this->app()->cUrl('http://partner.baibaobike.com/authed/register.html', $regform);
        $reginfo = json_decode($regjson, true);
        //die(print_r($reginfo,true));
        $authform = [
            'response_type' => 'code',
            'client_id' => $reginfo['data']['app_key'],
            'state' => $reginfo['data']['seed_secret'],
        ];
        $authjson = $this->app()->cUrl($reginfo['data']['authorize_url'], $authform);
        $authinfo = json_decode($authjson, true);
        //die(print_r($authinfo,true));
        $tokenform = [
            'client_id' => $reginfo['data']['app_key'],
            'client_secret' => $reginfo['data']['app_secret'],
            'grant_type' => 'authorization_code',
            'code' =>  $authinfo['data']['authorize_code'],
            'state' => $reginfo['data']['seed_secret'],
        ];
        $tokenjson = $this->app()->cUrl($reginfo['data']['token_url'], $tokenform);
        $tokeninfo = json_decode($tokenjson, true);
        //die(print_r($tokeninfo,true));
//        $srcform = [
//            'action' => 'login',
//            'client_id' => $reginfo['data']['app_key'],
//            'access_token' => $tokeninfo['data']['access_token'],
//            'state' => $reginfo['data']['seed_secret'],
//            'mobile'=> 15900545092,
//            'vericode' => 123456
//        ];
       
//        $srcform = [
//            
//            'action' => 'getAliPayOrder',
//            'client_id' => $reginfo['data']['app_key'],
//            'access_token' => $tokeninfo['data']['access_token'],
//            'state' => $reginfo['data']['seed_secret'],          
//            'total'=> '0.01',
//        ];
        
        $srcform = [
            'action' => 'verified',
            'client_id' => $reginfo['data']['app_key'],
            'access_token' => $tokeninfo['data']['access_token'],
            'state' => $reginfo['data']['seed_secret'],          
            'idno'=> '340123201707050001',
            'truename'=> '李清'
        ];
        $srcjson = $this->app()->cUrl($reginfo['data']['source_url'], $srcform);
        $srcinfo = json_decode($srcjson, true);
        return $res->write(print_r($srcinfo, true));
    }

    public function smsGet(Request $req, Response $res, $args) {
        $ress = $this->app()->smsVcode('15900545092');
        return $res->write('');
    }
    
    public function alipay(Request $req, Response $res, $args) {
        $pay = new Server();
        return $pay->getPrePayOrder();
    }
    public function notify(Request $req, Response $res, $args) {
        $post = $req->getParsedBody();
        $server = new Server();
        $flag = $server->notify();
         if($flag) {
            return $post;
        } else {
            return false;
        }
    }

}
