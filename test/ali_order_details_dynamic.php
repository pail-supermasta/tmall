<?php
header('Content-Type: application/json');
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
        return $res;
//        return json_decode($res, true);
    }


//    return $res;
}


require_once '../integration/ali_express/taobao/TopSdk.php';
define('APPKEY', '27862248');
define('SECRET', 'ca6916e55a087b3561b5077fc8b83ee6');
$shorten = findorderbyid('5002704147743467','500023000082eXbwToAg1af40ea9SRlzFmEOwoSCA3mTGhQaxQcZcjShLnX7vRkTL8r');
//var_dump($shorten['logistics_amount']['cent']);
//var_dump($shorten['order_amount']['cent']);
var_dump($shorten);
