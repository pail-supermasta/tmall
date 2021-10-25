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
        $res = json_encode((array)$result,JSON_UNESCAPED_UNICODE);

//        return json_decode($res, true);
    }


    return $res;
}


require_once '../integration/ali_express/taobao/TopSdk.php';
define('APPKEY', '32817975');
define('SECRET', 'fc3e140009f59832442d5c195c807fc0');
$shorten = findorderbyid('5013319238135280','50002301029r4LfaZzIzGMtdxRiWieuBmz1cGhQJ41a449a01ITXlFanTOWxJBdCZLQ5');
var_dump($shorten);
