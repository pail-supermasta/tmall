<?php

function findorderbyid($post_data, $sessionKey)
{

var_dump($post_data);
    $c = new TopClient;
    $c->format = "json";
    $c->appkey = APPKEY;
    $c->secretKey = SECRET;
    $req = new AliexpressSolutionOrderInfoGetRequest;
    $param1 = new OrderDetailQuery;
//$param1->ext_info_bit_flag="11111";
    $param1->order_id = $post_data;
    $req->setParam1(json_encode($param1));
    $resp = $c->execute($req, $sessionKey);
    var_dump($resp);

    if (!isset($resp->result)) {
        return false;
    } else {
        $result = $resp->result->data;
        $res = json_encode((array)$result);

        return json_decode($res, true);
    }


//    return $res;
}

/*
require_once 'taobao/TopSdk.php';
define('APPKEY', '27862248');
define('SECRET', 'ca6916e55a087b3561b5077fc8b83ee6');
$shorten = findorderbyid('5000456496969609','50002300d289mlqwnqAebm1lqueSpe9ECuAKVtDg1b6f0174jvgyezVnDsmJ4OkbLKm');
//var_dump($shorten['logistics_amount']['cent']);
//var_dump($shorten['order_amount']['cent']);
var_dump($shorten);*/
