<?php

function findorderbyid($post_data, $sessionKey)
{

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
define('APPKEY', '30833672');
define('SECRET', '1021396785b2eaa1497b7a58dddf19b3');
$shorten = findorderbyid('5000456496969609','50002300413yAdDbqygrAkmv21cf1a94bsqga2hwEpqARrGXkfThpxxhkZxBBRHfZ7x');
//var_dump($shorten['logistics_amount']['cent']);
//var_dump($shorten['order_amount']['cent']);
var_dump($shorten);*/
