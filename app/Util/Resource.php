<?php

namespace Util;

class Resource {

    protected $app;
    protected $post;
    private static $rd;

    public function __construct($app) {
        $request = \OAuth2\Request::createFromGlobals();
        $this->post = $request->request;
        $this->app = $app;
    }

    /*
     * 发送手机验证码
     */

    protected function sendSmsCode() {
        $post = $this->post;
        if ($post['mobile']) {
            $mobileCheck = $this->validateMobile($post['mobile']);
            if (!$mobileCheck) {
                $return = array('status' => false, 'errno' => '40010', 'errmsg' => "手机号格式错误");
                return $return;
            }
            $rs = $this->app->smsVcode($post['mobile']);
            if ($rs) {
                $return = array('status' => true, 'errno' => '0', 'errmsg' => "发送成功");
            } else {
                $return = array('status' => true, 'errno' => '40013', 'errmsg' => "发送失败");
            }
        } else {
            $return = array('status' => true, 'errno' => '40001', 'errmsg' => "请求参数无效");
        }
        return $return;
    }

    /*
     * 用户登录
     */

    protected function login() {
        $post = $this->post;
        if ($post['mobile'] && $post['vericode']) {
            $mobileCheck = $this->validateMobile($post['mobile']);
            if (!$mobileCheck) {
                $return = array('status' => false, 'errno' => '40010', 'errmsg' => "手机号格式错误");
                return $return;
            }
            $vericodeCheck = $this->app->codeVerified($post['mobile'], $post['vericode']);
            if (!$vericodeCheck) {
                $return = array('status' => true, 'errno' => '40014', 'errmsg' => "验证码错误或已过期");
                return $return;
            }
            $row = $this->app->no()->client()->where(array('mobile' => $post['mobile']))->fetch();
            if ($row) {
//                $user = $row->toArray();
                $this->app->setTokenUserId($post['access_token'], $post['client_id'], $row['id']);
                $return = ['status' => true, 'errno' => '0', 'message' => "登陆成功"];
            } else {
                $client['mtime'] = $client['ctime'] = time();
                $client['passwd'] = md5(uniqid());
                $client['idno'] = 0;
                $client['agent_id'] = 0;
                $client['truename'] = 0;
                $client['mobile'] = $post['mobile'];
                $rs = $this->app->no()->client()->insert($client);
                if ($rs) {
                    $this->app->setTokenUserId($post['access_token'], $post['client_id'], $rs['id']);
                    $return = ['status' => true, 'errno' => '0', 'message' => "注册成功"];
                } else {
                    $return = ['status' => false, 'errno' => '40012', 'errmsg' => '注册失败'];
                }
            }
        } else {
            $return = array('status' => false, 'errno' => '40001', 'errmsg' => "请求参数无效");
        }
        return $return;
    }

    /*
     * 用户登出
     */

    protected function logout() {
        $post = $this->post;
        $this->app->delTokenUserId($post['access_token'], $post['client_id']);
        $return = ['status' => true, 'errno' => '0', 'message' => "登出成功"];
        return $return;
    }

    /*
     * 返回用户相关状态
     */

    protected function initUserStatus() {
        $post = $this->post;
        if ($post) {
            $user_id = $this->app->getTokenUserId($post['access_token'], $post['client_id']);
//            print_r($user_id);die;
            $clientRow = $this->app->no()->client()->where(array('id' => $user_id))->fetch();
            if ($clientRow['status'] == 0) {
                $deposit = $this->app->no()->deposit()->where(['client_id' => $user_id, 'status' => 0])->fetch();
                $is_paydeposit = isset($deposit) ? 1 : 0;
                $userStatus = [
                    'balance' => $clientRow['balance'],
                    'is_verified' => $clientRow['is_verified'],
                    'is_paydeposit' => $is_paydeposit
                ];
                $return = ['status' => true, 'errno' => '0', 'data' => $userStatus];
            } else {
                $return = ['status' => false, 'errno' => '40021', 'errmsg' => '该账号已锁定'];
            }
        } else {
            $return = ['status' => false, 'errno' => '40001', 'errmsg' => '请求参数无效'];
        }
        return $return;
    }

    /*
     * 支付宝充值
     */

    protected function getAliPayOrder() {
        $post = $this->post;
        $config = [
            'appId' => "2017061107466058",
            'rsaPrivateKey' => 'MIIEvgIBADANBgkqhkiG9w0BAQEFAASCBKgwggSkAgEAAoIBAQC0J5GGpOqZuvUnD8QI6xDeyq6jaYe/VdU/t+6fmPv26+og8EgY/fJHzObJxQc+1doyRPIu59G1To6q+MC/wFZhxNDewa70i+8AjPS8QDa9pOqgvwJH5BlayclRCZXKzecERMA0DBteFB0baYif5G+8iBqQwPC5J1HdD82wHjXC8Lwtarg6Ix52/eB0XL/i6dYDbUuZekLJIZiDIkkWreB/En62tjgNKD6Bs0oi7tarPsDoibPeRxL3DnHFrbMkBrZaWXslLcj2pXeGmGb5vjmsY88cWYSgGJsBtLZ7ToU6iG54wFUPiskM3NzvBhBPKge/64alkbZTQ0q+YVScdVh7AgMBAAECggEBAI3deuOkinl0mAiiiaTcNvS6druIJrWtSbhbhzV2qzPOoxg9HwlPMLMJz9OjrAj3LlPXpz74nlNAAWjxaheVxnBHJJPFwZgheZvdY/u6NWExtPHQeGNUZALyU+3Utnh1nC3oVdKmlgaHoEQt3sDKipLUOtcymF21cOm7wCWoJH3U8MKJnoONBUnMBJcoiMZ1Vs5r+sJ0Z3fk4eP/1Z+Zg8DBSNsl9NSlbBmxVflw50aBa6I4CMnMegw/1t7L6TZtPTiP8o7hZ0G9KxJPeGnBo/um06mG3Clj3VF6DMd4eblgs8mEfWe+qTiP0IdPXUrR7eq6dszFNlC75a9+VMaRkgECgYEA7oYwyqu6RPpy00nd4gmyvyIbV2c4xygIJJ3uggXZCUSUVLX5RUapeOeC2wGjKe17Io/E6nQOWoqdkQyk3jM8kTGruXPyAd3xmzdSW3A42gjkW9QW+2Ery1JzW4GlVN0k86nBWPpqmYsp9Aqtj7sMgM5rRKOGEnPnCu4GL0pcA9sCgYEAwVqVymkW29Vtp1yC6ifMZK5uhTVlHAKAYjEl/9D8CBuN3vBaxVMVaGO3UiWqmERyiLN3tLsIAmP6ms6HgJnZpLakQbfNJgtOpKLLm2ypI88YrDoKZSHgW2J4Z8kaAQNLXbWY2BF+jH1UGYxyWm5/vaTo0SaxgMM2rTSJokzeb+ECgYAW8oAFL4pHEpUzcJrRIT+6FazttreGqXpHE46bobZkpt1iXPNzT74ELLmxGjI5WWiMRaqbJ7ktysIn70B5RBKioVW1DMuOlGynEyZwN5awm0Rk9T2Ux59v+ymv9wQR6wigDIfWaJkS1omdud1Cw6sLRVCalOTUJ6Rlr8qWiB/cGwKBgQCn/7wsvbil08DN7Py21VOrmz/eMDGk76t7JbcdmgiSRtazAWXtE66DIDkVgDLE0JwvmLgG6YchBJunTJHBtGu9yQ/ZJgly59oyBF0is3wW6AdJBbkofBHDdUCm9L3KaYFfb7zY6AJrsS2UcUqetmn5bkL4D0WlWni0b/Syd1XCIQKBgEnLVMR4mYbsnDj94owqjgC7y0A6Pq4+XGmwd+jj/uw/Tlh0z6J6dzT175faKFeuGGX0mb7cOikY/IAMUzvahYqdzAqdVDnHSJ+KbiF0bw32uN4O2iMFtHnCDzzus7NmYvjCGYxfnGalX6viBUFPUTOd+sX4zaAjkMOwVKbbmhcO'
        ];
        if ($post['total'] && $post['type']) {
            $now = date('Y-m-d H:i:s', time());
            $orderid = $post['type'] . "ali" . $this->buildOrderNo();
//            $bizcontent = "{\"body\":\"百宝单车\","
//            . "\"subject\": \"充值\","
//            . "\"out_trade_no\": \"".$orderid."\","
//            . "\"timeout_express\": \"30m\","
//            . "\"total_amount\": \"".$post['total']."\","
//            . "\"createtime\": \"".$now."\","
//            . "\"product_code\":\"1\""
//            . "}";
//            $server = $this->app->getAlipayOrder($bizcontent);

            $order = [
                'appId' => $config['appId'],
                'private_key' => $config['rsaPrivateKey'],
                'body' => '百宝单车',
                'subject' => '充值',
                'out_trade_no' => $orderid,
                'timeout_express' => '30m',
                'total_amount' => $post['total'],
                'product_code' => $post['type'],
                'createtime' => $now
            ];
            $return = ['status' => true, 'errno' => '0', 'data' => $order];
//            $deposit = $this->createOrderforDepositRecharge($post, $order);
//            if ($deposit) {
//                $return = ['status' => true, 'errno' => '0', 'data' => $order];
//            } else {
//                $return = ['status' => false, 'errno' => '-1', 'errmsg' => '系统繁忙'];
//            }
        } else {
            $return = array('status' => false, 'errno' => '40001', 'errmsg' => "请求参数无效");
        }
        //$rs = $this->app->getAlipayOrder($order);
        return $return;
    }

    protected function alipayTest() {
        $post = $this->post;
        $config = [
            'appId' => "2017061107466058",
            'rsaPrivateKey' => 'MIIEvgIBADANBgkqhkiG9w0BAQEFAASCBKgwggSkAgEAAoIBAQC0J5GGpOqZuvUnD8QI6xDeyq6jaYe/VdU/t+6fmPv26+og8EgY/fJHzObJxQc+1doyRPIu59G1To6q+MC/wFZhxNDewa70i+8AjPS8QDa9pOqgvwJH5BlayclRCZXKzecERMA0DBteFB0baYif5G+8iBqQwPC5J1HdD82wHjXC8Lwtarg6Ix52/eB0XL/i6dYDbUuZekLJIZiDIkkWreB/En62tjgNKD6Bs0oi7tarPsDoibPeRxL3DnHFrbMkBrZaWXslLcj2pXeGmGb5vjmsY88cWYSgGJsBtLZ7ToU6iG54wFUPiskM3NzvBhBPKge/64alkbZTQ0q+YVScdVh7AgMBAAECggEBAI3deuOkinl0mAiiiaTcNvS6druIJrWtSbhbhzV2qzPOoxg9HwlPMLMJz9OjrAj3LlPXpz74nlNAAWjxaheVxnBHJJPFwZgheZvdY/u6NWExtPHQeGNUZALyU+3Utnh1nC3oVdKmlgaHoEQt3sDKipLUOtcymF21cOm7wCWoJH3U8MKJnoONBUnMBJcoiMZ1Vs5r+sJ0Z3fk4eP/1Z+Zg8DBSNsl9NSlbBmxVflw50aBa6I4CMnMegw/1t7L6TZtPTiP8o7hZ0G9KxJPeGnBo/um06mG3Clj3VF6DMd4eblgs8mEfWe+qTiP0IdPXUrR7eq6dszFNlC75a9+VMaRkgECgYEA7oYwyqu6RPpy00nd4gmyvyIbV2c4xygIJJ3uggXZCUSUVLX5RUapeOeC2wGjKe17Io/E6nQOWoqdkQyk3jM8kTGruXPyAd3xmzdSW3A42gjkW9QW+2Ery1JzW4GlVN0k86nBWPpqmYsp9Aqtj7sMgM5rRKOGEnPnCu4GL0pcA9sCgYEAwVqVymkW29Vtp1yC6ifMZK5uhTVlHAKAYjEl/9D8CBuN3vBaxVMVaGO3UiWqmERyiLN3tLsIAmP6ms6HgJnZpLakQbfNJgtOpKLLm2ypI88YrDoKZSHgW2J4Z8kaAQNLXbWY2BF+jH1UGYxyWm5/vaTo0SaxgMM2rTSJokzeb+ECgYAW8oAFL4pHEpUzcJrRIT+6FazttreGqXpHE46bobZkpt1iXPNzT74ELLmxGjI5WWiMRaqbJ7ktysIn70B5RBKioVW1DMuOlGynEyZwN5awm0Rk9T2Ux59v+ymv9wQR6wigDIfWaJkS1omdud1Cw6sLRVCalOTUJ6Rlr8qWiB/cGwKBgQCn/7wsvbil08DN7Py21VOrmz/eMDGk76t7JbcdmgiSRtazAWXtE66DIDkVgDLE0JwvmLgG6YchBJunTJHBtGu9yQ/ZJgly59oyBF0is3wW6AdJBbkofBHDdUCm9L3KaYFfb7zY6AJrsS2UcUqetmn5bkL4D0WlWni0b/Syd1XCIQKBgEnLVMR4mYbsnDj94owqjgC7y0A6Pq4+XGmwd+jj/uw/Tlh0z6J6dzT175faKFeuGGX0mb7cOikY/IAMUzvahYqdzAqdVDnHSJ+KbiF0bw32uN4O2iMFtHnCDzzus7NmYvjCGYxfnGalX6viBUFPUTOd+sX4zaAjkMOwVKbbmhcO'
        ];
        if ($post['total'] && $post['type']) {
            $now = date('Y-m-d H:i:s', time());
            $orderid = $post['type'] . "ali" . $this->buildOrderNo();
            $bizcontent = "{\"body\":\"百宝单车\","
                    . "\"subject\": \"充值\","
                    . "\"out_trade_no\": \"" . $orderid . "\","
                    . "\"timeout_express\": \"30m\","
                    . "\"total_amount\": \"" . $post['total'] . "\","
                    . "\"createtime\": \"" . $now . "\","
                    . "\"product_code\":\"1\""
                    . "}";
            $server = $this->app->getAlipayOrder($bizcontent);

            $return = ['status' => true, 'errno' => '0', 'data' => $server];
//            $deposit = $this->createOrderforDepositRecharge($post, $order);
//            if ($deposit) {
//                $return = ['status' => true, 'errno' => '0', 'data' => $order];
//            } else {
//                $return = ['status' => false, 'errno' => '-1', 'errmsg' => '系统繁忙'];
//            }
        } else {
            $return = array('status' => false, 'errno' => '40001', 'errmsg' => "请求参数无效");
        }
        //$rs = $this->app->getAlipayOrder($order);
        return $return;
    }

    /*
     * 实名认证
     */

    protected function verified() {
        $post = $this->post;
        if ($post['idno'] && $post['truename']) {
            if (preg_match('/(^[1-9]\d{5}(18|19|([23]\d))\d{2}((0[1-9])|(10|11|12))(([0-2][1-9])|10|20|30|31)\d{3}[0-9Xx]$)|(^[1-9]\d{5}\d{2}((0[1-9])|(10|11|12))(([0-2][1-9])|10|20|30|31)\d{2}$)/', $post['idno'])) {
//                if (preg_match('/^[x{4e00}-x{9fa5}]{1,20}$/', $post['truename'] )) {
//                $tokenRow = $this->app->no()->oauth_access_tokens()->where(array('access_token' => $post['access_token']))->fetch();
//                $client = $this->app->no()->client[$tokenRow['user_id']];
                $user_id = $this->app->getTokenUserId($post['access_token'], $post['client_id']);
                $clientRow = $this->app->no()->client()->where(array('id' => $user_id))->fetch();
                $data = [
                    'is_verified' => 1,
                    'idno' => $post['idno'],
                    'truename' => $post['truename'],
                    'mtime' => time()
                ];
                $rs = $clientRow->update($data);
                if ($rs) {
                    $return = ['status' => true, 'errno' => '0', 'message' => "认证成功"];
                } else {
                    $return = ['status' => false, 'errno' => '0', 'message' => "认证失败"];
                }

//                }else{
//                    $return = ['status' => false, 'errno' => '40019', 'errmsg' => '认证失败,姓名信息不合法'];
//                }     
            } else {
                $return = ['status' => false, 'errno' => '40018', 'errmsg' => '认证失败,身份证信息不合法'];
            }
        } else {
            $return = ['status' => false, 'errno' => '40001', 'errmsg' => '请求参数无效'];
        }
        return $return;
    }

    /*
     * 获取范围内单车
     */

    protected function searchBikes() {
        $post = $this->post;
        $range = isset($post['range']) ? $post['range'] : 9000;
        if ($post['lat'] && $post['lng']) {

            $listObj = $this->app->no()->device()->select('
                id,lat,lng,action,
                (    
                    3971 * acos (    
                      cos ( radians(' . $post["lat"] . ') )    
                      * cos( radians( lat ) )    
                      * cos( radians( lng ) - radians(' . $post["lng"] . ') )    
                      + sin ( radians(' . $post["lat"] . ') )    
                      * sin( radians( lat ) )    
                    )    
                ) AS distance')->where('status = 0')->group("distance HAVING distance < " . $range)->order('distance');
            $listArr = $this->app->iterator_array($listObj);
            if ($listArr) {
                $return = ['status' => true, 'errno' => '0', 'data' => $listArr];
            } else {
                $return = ['status' => false, 'errno' => '50020', 'errmsg' => '该区域未找到车辆'];
            }
        } else {
            $return = ['status' => false, 'errno' => '40001', 'errmsg' => '请求参数无效'];
        }
        return $return;
    }

    /*
     * 扫码开锁
     */

    protected function scanCode() {
        $post = $this->post;
        if ($post['sn']) {
            $now = time();
            $deviceRow = $this->app->no()->device()->where(array('imei' => $post['sn']))->fetch();
            $actionArr = ["", "可使用", "开锁中", "使用中", "关锁中", "结账中", "车辆故障"];
            if (isset($deviceRow['status']) && $deviceArr['status'] == 0) {
                if ($deviceRow['action'] == 1) {
                    $deviceData = ['action' => 3];
                    $device = $deviceRow->update($deviceData);
                    $this->redis()->rpush('waitting_unlock', json_encode(array(
                        'uid' => 1,
                        'did' => 1,
                    )));
                    $user_id = $this->app->getTokenUserId($post['access_token'], $post['client_id']);
                    $clientRow = $this->app->no()->client()->where(array('id' => $user_id))->fetch();
                    $revenueData = [
                        'agent_id' => $clientRow['agent_id'],
                        'client_id' => $user_id,
                        'device_id' => $post['sn'],
                        'start' => $now,
                        'over' => $now,
                        'profit' => 0,
                        'ctime' => $now
                    ];
                    $revenue = $this->operateData('revenue', $revenueData);
                    $return = ['status' => true, 'errno' => '0', 'message' => "开锁成功"];
                } else {
                    $return = ['status' => false, 'errno' => '5000' . $deviceRow['action'], 'errmsg' => $actionArr[$deviceRow['action']]];
                }
            } else {
                $return = ['status' => false, 'errno' => '50009', 'errmsg' => '该车损坏已禁用'];
            }
        } else {
            $return = ['status' => false, 'errno' => '40001', 'errmsg' => '请求参数无效'];
        }
        return $return;
    }

    /*
     * 余额行程结账
     */

    protected function balanceCheckout() {
        $post = $this->post;
        $pay_amount = isset($post['total']) ? $post['total'] : 1.00;
        if ($post['sn'] && $post['total']) {
            $deviceRow = $this->app->no()->device()->where(array('binding' => $post['sn']))->fetch();
            $actionArr = ["", "可使用", "开锁中", "使用中", "关锁中", "结账中", "车辆故障"];
            if ($deviceRow['status'] == 0) {
                if ($deviceRow['action'] == 4) {
                    $deviceRow->update(array('action' => 5));
                    $user_id = $this->app->getTokenUserId($post['access_token'], $post['client_id']);
                    $clientRow = $this->app->no()->client()->where(array('id' => $user_id))->fetch();
                    if ($clientRow['balance'] >= $post['total']) {
                        $now = date('Y-m-d H:i:s', time());
                        $orderid = $this->buildOrderNo();
                        $order = [
                            'body' => '百宝单车',
                            'subject' => '行程支付',
                            'out_trade_no' => $orderid,
                            'timeout_express' => '30m',
                            'total_amount' => $pay_amount,
                            'product_code' => '3',
                            'createtime' => $now
                        ];
                        $creteOrderforPay = $this->createOrderforPay($post, $order);
                        if ($creteOrderforPay) {
                            $deviceRow->update(array('action' => 1));
                            $return = ['status' => true, 'errno' => '0', 'data' => $order];
                        } else {
                            $return = ['status' => false, 'errno' => '-1', 'errmsg' => '系统繁忙'];
                        }
                    } else {
                        $return = ['status' => false, 'errno' => '40020', 'errmsg' => '余额不足，请先充值'];
                    }
                } else {
                    $return = ['status' => false, 'errno' => '5000' . $deviceRow['action'], 'errmsg' => $actionArr[$deviceRow['action']]];
                }
            } else {
                $return = ['status' => false, 'errno' => '50009', 'errmsg' => '该车损坏已禁用'];
            }
        } else {
            $return = ['status' => false, 'errno' => '40001', 'errmsg' => '请求参数无效'];
        }
        return $return;
    }

    /*
     * 获取用户信息
     */

    protected function getUserInfo() {
        $post = $this->post;
        if ($post) {
            $user_id = $this->app->getTokenUserId($post['access_token'], $post['client_id']);
            $clientRow = $this->app->no()->client()->where(array('id' => $user_id))->fetch();
            if ($clientRow['status'] == 0) {
                $userInfo = [
                    'mobile' => $clientRow['mobile'],
                    'deposit' => 99.00,
                    'balance' => $clientRow['balance'],
                    'integral' => $clientRow['integral'],
                    'idno' => $clientRow['idno'],
                    'truename' => $clientRow['truename'],
                    'nickname' => $clientRow['nickname'],
                    'is_verified' => $clientRow['is_verified']
                ];
                $return = ['status' => true, 'errno' => '0', 'data' => $userInfo];
            } else {
                $return = ['status' => false, 'errno' => '40021', 'errmsg' => '该账号已锁定'];
            }
        } else {
            $return = ['status' => false, 'errno' => '40001', 'errmsg' => '请求参数无效'];
        }
        return $return;
    }

    /*
     * 获取用户信用记录
     */

    protected function getUserIntegral() {
        $post = $this->post;
        if ($post) {
            $user_id = $this->app->getTokenUserId($post['access_token'], $post['client_id']);
            $integralObj = $this->app->no()->integral()->select('current,changed,direction,note')->where(array('client_id' => $user_id));
            $integralArr = $this->app->iterator_array($integralObj);
            if ($integralArr) {
                $return = ['status' => true, 'errno' => '0', 'data' => $integralArr];
            } else {
                $return = ['status' => false, 'errno' => '40022', 'errmsg' => '您还没有信用记录'];
            }
        } else {
            $return = ['status' => false, 'errno' => '40001', 'errmsg' => '请求参数无效'];
        }
        return $return;
    }

    /*
     * 获取用户充值记录
     */

    protected function getUserRecharge() {
        $post = $this->post;
        if ($post) {
            $user_id = $this->app->getTokenUserId($post['access_token'], $post['client_id']);
            $balanceObj = $this->app->no()->balance()->select('current,changed,direction,payment_id,paidtype,note')->where(array('client_id' => $user_id, 'cases' => 2));
            $balanceArr = $this->app->iterator_array($balanceObj);
            if ($balanceArr) {
                $return = ['status' => true, 'errno' => '0', 'data' => $balanceArr];
            } else {
                $return = ['status' => false, 'errno' => '40022', 'errmsg' => '您还没有充值记录'];
            }
        } else {
            $return = ['status' => false, 'errno' => '40001', 'errmsg' => '请求参数无效'];
        }
        return $return;
    }

    /*
     * 用户指南
     */

    protected function userGuide() {
        $post = $this->post;
        if ($post) {
            if ($post['type'] == 1) {
                $url = "https://www.baibaobike.com/index";
            } else if ($post['type'] == 2) {
                $url = "https://www.baibaobike.com/product";
            } else if ($post['type'] == 3) {
                $url = "https://www.baibaobike.com/about";
            } else if ($post['type'] == 4) {
                $url = "https://www.baibaobike.com/contact";
            } else if ($post['type'] == 5) {
                $url = "https://www.baibaobike.com/contact";
            } else {
                $url = "https://www.baibaobike.com/downlaod";
            }
            $data = ["url" => $url];
            $return = ['status' => true, 'errno' => '0', 'data' => $data];
        } else {
            $return = ['status' => false, 'errno' => '40001', 'errmsg' => '请求参数无效'];
        }
        return $return;
    }

    /*
     * 用户昵称修改
     */

    protected function modifyNickname() {
        $post = $this->post;
        if ($post['nickname']) {
            $user_id = $this->app->getTokenUserId($post['access_token'], $post['client_id']);
            $clientRow = $this->app->no()->client()->where(array('id' => $user_id, 'status' => 0))->fetch();
            if ($clientRow) {
                $clientRow->update(array('nickname' => $post['nickname']));
                $return = ['status' => true, 'errno' => '0', 'message' => '修改成功'];
            } else {
                $return = ['status' => false, 'errno' => '40021', 'errmsg' => '该账号已锁定'];
            }
        } else {
            $return = ['status' => false, 'errno' => '40001', 'errmsg' => '请求参数无效'];
        }
        return $return;
    }

    /*
     * 用户信息修改
     */

    protected function modifyMobile() {
        $post = $this->post;
        if ($post['mobile'] && $post['idno'] && $post['vericode']) {
            $mobileCheck = $this->validateMobile($post['mobile']);
            if (!$mobileCheck) {
                $return = array('status' => false, 'errno' => '40010', 'errmsg' => "手机号格式错误");
                return $return;
            }
            $vericodeCheck = $this->app->codeVerified($post['mobile'], $post['vericode']);
            if (!$vericodeCheck) {
                $return = array('status' => true, 'errno' => '40014', 'errmsg' => "验证码错误或已过期");
                return $return;
            }
            $user_id = $this->app->getTokenUserId($post['access_token'], $post['client_id']);
            $clientRow = $this->app->no()->client()->where(array('id' => $user_id, 'status' => 0))->fetch();
            if ($clientRow) {
                if ($client['idno'] == $post['idno']) {
                    $clientRow->update(array('mobile' => $post['mobile']));
                    $return = ['status' => true, 'errno' => '0', 'message' => '修改成功'];
                } else {
                    $return = ['status' => false, 'errno' => '40023', 'errmsg' => '身份证不匹配'];
                }
            } else {
                $return = ['status' => false, 'errno' => '40021', 'errmsg' => '该账号已锁定'];
            }
        } else {
            $return = ['status' => false, 'errno' => '40001', 'errmsg' => '请求参数无效'];
        }
        return $return;
    }

    /*
     * 创建支付订单
     */

    public function createOrderforPay($post, $order) {
        $now = time();
        $user_id = $this->app->getTokenUserId($post['access_token'], $post['client_id']);
        $clientRow = $this->app->no()->client()->where(array('id' => $user_id))->fetch();

        $paymentData = [
            'order_id' => $order['out_trade_no'],
            'agent_id' => $clientRow['agent_id'],
            'client_id' => $user_id,
            'amount' => $order['total_amount'],
            'brief' => $order['subject'],
            'ctime' => $now
        ];
        $payment = $this->operateData("payment", $paymentData);
        $paymentRow = $this->app->no()->payment()->where(array('order_id' => $order['out_trade_no']))->fetch();

        if ($paymentRow['status'] == 0) {
            $clientData = [
                'balance' => $clientRow['balance'] - $order['total_amount'],
                'integral' => $clientRow['integral'] + 1,
                'mtime' => $now
            ];
            $client = $clientRow->update($clientData);
            //更新后的用户信息
            $clientRow = $this->app->no()->client()->where(array('id' => $user_id))->fetch();

            $balanceData = [
                'agent_id' => $clientRow['agent_id'],
                'client_id' => $user_id,
                'current' => $clientRow['balance'],
                'direction' => 2, //1加2减
                'changed' => $order['total_amount'],
                'cases' => 4, //1.押金充值 2.余额充值 3.设备购买 4.设备使用消费 5.用户提现 
                'payment_id' => $paymentRow['id'],
                'paidtype' => 3, //1支付宝2微信3余额
                'note' => $order['subject'],
                'ctime' => $now
            ];
            $balance = $this->operateData("balance", $balanceData);

            $integralData = [
                'agent_id' => $clientRow['agent_id'],
                'client_id' => $user_id,
                'current' => $clientRow['integral'],
                'direction' => 1, //1加2减
                'changed' => 1,
                'note' => $order['subject'],
                'ctime' => $now
            ];
            $integral = $this->operateData("integral", $integralData);

            $revenueData = [
                'over' => $now,
                'profit' => $order['total_amount'],
            ];
            $revenueRow = $this->app->no()->revenue()->select('')->where(array('client_id' => $user_id, 'device_id' => $post['sn']))->limit(1)->order('id DESC');
            $revenue = $revenueRow->update($revenueData);

            $deviceRow = $this->app->no()->device()->where(array('binding' => $post['sn']))->fetch();
            $deviceData = [
                'dynamic_revenue' => $order['total_amount'],
                'revenue' => $order['total_amount'] + $deviceRow['revenue'],
                'mtime' => $now
            ];
            $device = $deviceRow->update($deviceData);

            $agentRow = $this->app->no()->agent()->where(array('id' => $clientRow['agent_id']))->fetch();
            $agentData = [
                'dynamic_revenue' => $order['total_amount'] + $agentRow['dynamic_revenue'],
                'revenue' => $order['total_amount'] + $agentRow['revenue'],
            ];
            $agent = $agentRow->update($agentData);
        }
        if ($payment && $client && $balance && $integral && $revenue && $device && $agent) {
            return true;
        } else {
            return false;
        }
    }

    /*
     * 创建押金/余额充值订单
     */

    public function createOrderforRecharge($post, $order) {
        $now = time();
        $user_id = $this->app->getTokenUserId($post['access_token'], $post['client_id']);
        $clientRow = $this->app->no()->client()->where(array('id' => $user_id))->fetch();
        $paymentData = [
            'order_id' => $order['out_trade_no'],
            'agent_id' => $clientRow['agent_id'],
            'client_id' => $user_id,
            'amount' => $order['total_amount'],
            'brief' => $order['subject'],
            'ctime' => $now
        ];
        $payment = $this->operateData("payment", $paymentData);
        $paymentRow = $this->app->no()->payment()->where(array('order_id' => $order['out_trade_no']))->fetch();
        if ($paymentRow['status'] == 0) {
            if ($order['product_code'] == 2) {
                $clientData = [
                    'balance' => $clientRow['balance'] + $order['total_amount'],
                    'integral' => $clientRow['integral'] + 1,
                    'mtime' => $now
                ];
                $client = $clientRow->update($clientData);

                $integralData = [
                    'agent_id' => $clientRow['agent_id'],
                    'client_id' => $user_id,
                    'current' => $clientRow['integral'],
                    'direction' => 1, //1加2减
                    'changed' => 1,
                    'note' => $order['subject'],
                    'ctime' => $now
                ];
                $integral = $this->operateData("integral", $integralData);
                $clientRow = $this->app->no()->client()->where(array('id' => $user_id))->fetch();
            }
            $balanceData = [
                'agent_id' => $clientRow['agent_id'],
                'client_id' => $user_id,
                'current' => $clientRow['balance'],
                'direction' => 1, //1加2减
                'changed' => $order['total_amount'],
                'cases' => 1, //1.押金充值 2.余额充值 3.设备购买 4.设备使用消费 5.用户提现 
                'payment_id' => $paymentRow['id'],
                'paidtype' => 1, //1支付宝2微信3余额
                'note' => $order['subject'],
                'ctime' => $now
            ];
            $balance = $this->operateData("balance", $balanceData);

            if ($order['product_code'] == 1) {
                $depositData = [
                    'agent_id' => $clientRow['agent_id'],
                    'client_id' => $user_id,
                    'amount' => $order['total_amount'],
                    'payment_id' => $paymentRow['id'],
                    'paidtype' => 1,
                    'ctime' => $now
                ];
                $deposit = $this->operateData("deposit", $depositData);
            }
        }
        if ($payment && $clientRow && $balance) {
            return true;
        } else {
            return false;
        }
    }

    /*
     * 接收支付宝返回的订单信息
     */

    protected function getAlipayNotifyOrder() {
        $post = $this->post;
        $write = json_encode($post) . PHP_EOL;
        $path = dirname(dirname(__FILE__)) . "/Tmpl/Main/Logs/order.txt";
        file_put_contents($path, $write, FILE_APPEND);
//        $json ='{"client_id":"cf83083ce4ecfc81bbb314d1b50cb7b5","data":{"result":"{\"alipay_trade_app_pay_response\":{\"code\":\"10000\",\"msg\":\"Success\",\"app_id\":\"2017061107466058\",\"auth_app_id\":\"2017061107466058\",\"charset\":\"utf-8\",\"timestamp\":\"2017-07-10 16:57:58\",\"total_amount\":\"0.01\",\"trade_no\":\"2017071021001004230204118077\",\"seller_id\":\"2088621890366270\",\"out_trade_no\":\"ali2017071010210056\"},\"sign\":\"m\/cZ1QYukWCHfzSndm7h0OkSa1QFDWePC3RgWyE4Jxzu6QVWHBLWy5clIMI+vrUohJz4FDsokYPHhBYZj6AMWrP66CnjeZWsce8ieeXhDNsiNZWi8o1RWFTNEdqjjkt6yM5l\/B149mnDlyiDoSemsDb\/OZ0br0FfHtHu2cEeVqhO8rOxGJ7rN0QPcpbQ1kaVcdL6R5Gy+inZZr7\/JzjWATDW2UE8EBzjLkTjGUUSbSYWcpve4LM5nay+6jZza\/hewHaFAQ1HT90D+JN8MrYEj\/JE9pKiw2ufLTO4\/eNSHezzDvnALSd\/SqxOIcGOFxiRM633d02rivxpSpUscQqfnQ==\",\"sign_type\":\"RSA2\"}","resultStatus":"9000","memo":""},"state":"bd79f6ad32f57ec726a9a5d4d9c85e2f","access_token":"7c5eebca3bb8253b1db7a32780e4f3db71a544fe","action":"getAlipayNotifyOrder"}';  
        if ($post['data']) {
            $notifyJson = json_encode($post);
            $notify = json_decode($notifyJson, true);
            if (strpos($_SERVER['HTTP_USER_AGENT'], 'iPhone') || strpos($_SERVER['HTTP_USER_AGENT'], 'iPad')) {
                if ($notify['data']['resultStatus'] == 9000) {
                    $alipayData = json_decode($notify['data']['result'], true);
                    $alipayOrder = $alipayData['alipay_trade_app_pay_response'];
                    $createtime = strtotime($alipayOrder['timestamp']);
                    $product_code = substr($alipayOrder['out_trade_no'], 0, 1);
                    if ($product_code == 1) {
                        $subject = "押金充值";
                    } else {
                        $subject = "余额充值";
                    }
                    $order = [
                        'body' => '百宝单车',
                        'subject' => $subject,
                        'out_trade_no' => $alipayOrder['out_trade_no'],
                        'timeout_express' => '30m',
                        'total_amount' => $alipayOrder['total_amount'],
                        'product_code' => $product_code,
                        'createtime' => $createtime
                    ];
                    $DepositRecharge = $this->createOrderforRecharge($post, $order);
                    if ($DepositRecharge) {
                        $return = ['status' => true, 'errno' => '0', 'message' => '创建订单成功'];
                    } else {
                        $return = ['status' => false, 'errno' => '40025', 'errmsg' => '创建订单失败，系统繁忙'];
                    }
                } else {
                    $return = ['status' => false, 'errno' => '40026', 'errmsg' => '创建订单失败，未支付'];
                }
            } else if (strpos($_SERVER['HTTP_USER_AGENT'], 'Android')) {
                $alipayData = json_decode($notify['data'], true);
                $alipayOrder = $alipayData['alipay_trade_app_pay_response'];
                $createtime = strtotime($alipayOrder['timestamp']);
                $product_code = substr($alipayOrder['out_trade_no'], 0, 1);
                if ($product_code == 1) {
                    $subject = "押金充值";
                } else {
                    $subject = "余额充值";
                }
                $order = [
                    'body' => '百宝单车',
                    'subject' => $subject,
                    'out_trade_no' => $alipayOrder['out_trade_no'],
                    'timeout_express' => '30m',
                    'total_amount' => $alipayOrder['total_amount'],
                    'product_code' => $product_code,
                    'createtime' => $createtime
                ];
                $DepositRecharge = $this->createOrderforRecharge($post, $order);
                if ($DepositRecharge) {
                    $return = ['status' => true, 'errno' => '0', 'message' => '创建订单成功'];
                } else {
                    $return = ['status' => false, 'errno' => '40025', 'errmsg' => '创建订单失败，系统繁忙'];
                }
            } else {
                $return = ['status' => false, 'errno' => '40027', 'errmsg' => '只支持iOS和Android客户端'];
            }
        } else {
            $return = ['status' => false, 'errno' => '40001', 'errmsg' => '请求参数无效'];
        }
        return $return;
    }

    /*
     * 故障报修
     */

    protected function faultRepair() {
        $post = $this->post;
        if ($post['sn'] && $post['type']) {
            $user_id = $this->app->getTokenUserId($post['access_token'], $post['client_id']);
            $repairData = [
                'device_id' => $post['sn'],
                'client_id' => $user_id,
                'picpath' => $post['picpath'],
                'type' => $post['type'],
                'content' => $post['content'],
                'ctime' => time()
            ];
            $repair = $this->operateData('repair', $repairData);
            if ($repair) {
                $return = ['status' => true, 'errno' => '0', 'message' => '报修成功'];
            } else {
                $return = ['status' => false, 'errno' => '40024', 'errmsg' => '报修失败'];
            }
        } else {
            $return = ['status' => false, 'errno' => '40001', 'errmsg' => '请求参数无效'];
        }
        return $return;
    }

    /*
     * 插入/更新表数据
     */

    public function operateData($table, $data, $is_update = null, $id = null) {

        if (!$is_update) {
            $data['id'] = 0;
            $insert = $this->app->no()->$table()->insert($data);
            if ($insert) {
                return $insert;
            } else {
                return FALSE;
            }
        } else {
            $obj = $this->app->no()->$table[$id];
            $rs = $obj->update($data);
            if ($rs) {
                return $rs;
            } else {
                return FALSE;
            }
        }
    }

    /*
     * 订单号生成
     */

    public function buildOrderNo() {
        return date('Ymd') . substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
    }

    /*
     * 手机号验证
     */

    public function validateMobile($mobile) {
        if (preg_match('/^1(3[0-9]|4[57]|5[0-35-9]|7[0135678]|8[0-9])\\d{8}$/', $mobile)) {
            return true;
        }
    }

    public function __call($method, $args) {
        if (!method_exists($this, $method)) {
            return array('status' => false, 'errno' => '40007', 'errmsg' => "请求资源无效");
        }
        return $this->$method();
    }

    public function redis() {
        if (!self::$rd) {
            $rd = new \Redis();
            $rd->connect('127.0.0.1', 6379);
            self::$rd = $rd;
        }
        return self::$rd;
    }

}
