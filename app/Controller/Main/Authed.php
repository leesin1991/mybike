<?php

/*
 * ./wkhtmltox/bin/wkhtmltopdf localhost/test.html /tmp/test.pdf
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Controller\Main;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Util\Resource;

class Authed extends Controller {

    public function actions() {
        $this->app()->post('/authed/register.html', array($this, 'registerGet'));
        $this->app()->post('/authed/authorize/{auth}.html', array($this, 'authorizeGet'));
        $this->app()->post('/authed/token/{auth}.html', array($this, 'tokenGet'));
        $this->app()->post('/authed/refresh/{auth}.html', array($this, 'refreshGet'));
        $this->app()->post('/authed/resource/{auth}.html', array($this, 'resourceGet'));
        return $this;
    }

    public function registerGet(Request $req, Response $res, $args) {
        $server = $this->app()->oauthServer();
        $request = \OAuth2\Request::createFromGlobals();
        $post = $request->request;
        $storage = $server->getStorage('client');
//        print_r(md5($post['imei']));die;
//        if (isset($post['imei']) && isset($post['code']) && strlen($post['imei']) === 15) {
        if (isset($post['imei']) && isset($post['code'])) {
            if (md5($post['imei']) == $post['code']) {
                $client_id = md5($post['imei']);
                $client_secret = md5(uniqid());
                $status = $storage->setClientDetails($client_id, $client_secret, 'http://baibaobike.com/');
                if ($status) {
                    $details = $storage->getClientDetails($client_id);
                    $data = $this->app()->client($details);
                    $return = [
                        'status' => true,
                        'errno' => '0',
                        'data' => $data
                    ];
                } else {
                    $return = [
                        'status' => false,
                        'errno' => '40002'
                    ];
                }
            } else {
                $return = array(
                    'status' => false,
                    'errno' => '40003'
                );
            }
        } else {
            $return = array(
                'status' => false,
                'errno' => '40001'
            );
        }
        return $res->withHeader('Content-type', 'application/json')->write(json_encode($return));
    }

    /*
     * authorization code will expired in 30s，可以修改 OAuth2/ResponseType/AuthorizationCode.php 中的 AuthorizationCode class
     * authed/authroize/********.html?response_type=code&client_id=app_key&state=seed
     */

    public function authorizeGet(Request $req, Response $res, $args) {
        $server = $this->app()->oauthServer();
        $request = \OAuth2\Request::createFromGlobals();
        $post = $request->request;
        if ($args['auth'] == substr(md5($post['client_id'] . $post['state'] . 'authorize'), 0, 8)) {
            $response = new \OAuth2\Response();
            if ($server->validateAuthorizeRequest($request, $response)) {
                $clientId = 0;
                $server->handleAuthorizeRequest($request, $response, true, $clientId);
                $code = substr($response->getHttpHeader('Location'), strpos($response->getHttpHeader('Location'), 'code=') + 5, 40);
                $authorize = $server->getStorage('authorization_code');
                $authed = $authorize->getAuthorizationCode($code);
                $data = array(
                    'app_key' => $request->request('client_id'),
                    'authorize_code' => $code,
                    'expire_time' => $authed['expires']
                );
                $return = [
                    'status' => true,
                    'errno' => '0',
                    'data' => $data
                ];
            } else {
                $return = array(
                    'status' => false,
                    'errno' => '40004',
                    'errmsg' => '授权失败'
                );
            }
        } else {
            $return = array(
                'status' => false,
                'errno' => '40003',
                'errmsg' => '请求参数不合法'
            );
        }
        return $res->withHeader('Content-type', 'application/json')->write(json_encode($return));
    }

    /*
     * 有效期为 1209600s，可以在 OAuth2/ResponseType/AccessToken.php 中的 AccessToken class 中的构造函数配置中进行修改。
     * curl -u app_key:app_secret /authed/token/********.html -d grant_type=authorization_code&code=$authcode
     */

    public function tokenGet(Request $req, Response $res, $args) {
        $server = $this->app()->oauthServer();
        $request = \OAuth2\Request::createFromGlobals();
        $post = $request->request;
        if ($args['auth'] == substr(md5($post['client_id'] . $post['state'] . 'token'), 0, 8)) {
            $response = new \OAuth2\Response();
            $resp = $server->handleTokenRequest(\OAuth2\Request::createFromGlobals(), $response);
            $body = $resp->getResponseBody();
            $data = json_decode($body, true);
            if (isset($data['access_token'])) {
                $return = [
                    'status' => true,
                    'errno' => '0',
                    'data' => $data
                ];
            } else {
                $return = [
                    'status' => false,
                    'errno' => '40005',
                    'data' => $data
                ];
            }
        } else {
            $return = [
                'status' => false,
                'errno' => '40003',
                'errmsg' => '请求参数不合法'
            ];
        }
        return $res->withHeader('Content-type', 'application/json')->write(json_encode($return));
    }

    /*
     * curl -u app_key:app_secret /authed/refresh/********.html -d "grant_type=refresh_token&refresh_token=xxx"
     */

    public function refreshGet(Request $req, Response $res, $args) {
        $post = $req->getParsedBody();
        if ($args['auth'] == substr(md5($post['client_id'] . $post['state'] . 'refresh'), 0, 8)) {
            $server = $this->app()->oauthServer();
            $data = $server->handleTokenRequest(OAuth2\Request::createFromGlobals())->getResponseBody();
        } else {
            $data = array(
                'status' => false,
                'errno' => '00005'
            );
        }
        return $res->withHeader('Content-type', 'application/json')->write(json_encode($data));
    }

    /*
     * curl /authed/resource/********.html -d access_token=xxx
     */

    public function resourceGet(Request $req, Response $res, $args) {
        $request = \OAuth2\Request::createFromGlobals();
        $post = $request->request;
        if ($args['auth'] == substr(md5($post['client_id'] . $post['state'] . 'resource'), 0, 8)) {
            $server = $this->app()->oauthServer();
//            if ($server->verifyResourceRequest($request)) {
                $source = new Resource($this->app());
                $action = $post['action'];
                $data = $source->$action();
//            } else {
//                $data = array('status' => false,'errno' => '40006','errmsg' => '获取资源失败');
//            }
        } else {
            $data = array('status' => false,'errno' => '40003','errmsg' => '请求参数不合法');
        }
        return $res->withHeader('Content-type', 'application/json')->write(json_encode($data));
    }

}
