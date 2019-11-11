<?php

function formMasterList($credential)
{

    $buyer_login_id = $credential['login'];
    $sessionKey = $credential['sessionKey'];


    /*catch all for previous 24 hours*/
    $offset = 24 * 60 * 60;
    $create_day = date("Y-m-d H:i:s", strtotime(gmdate("Y-m-d H:i:s")) - $offset);

    $c = new TopClient;
    $c->format = "json";
    $c->appkey = APPKEY;
    $c->secretKey = SECRET;
    $req = new AliexpressTradeSellerOrderlistGetRequest;
    $param_aeop_order_query = new AeopOrderQuery;
    $param_aeop_order_query->buyer_login_id = $buyer_login_id;
    $param_aeop_order_query->create_date_start = $create_day;
    $param_aeop_order_query->current_page = "1";
    $param_aeop_order_query->order_status_list = array('WAIT_SELLER_SEND_GOODS', 'PLACE_ORDER_SUCCESS','RISK_CONTROL', 'PAYMENT_PROCESSING');
    $param_aeop_order_query->page_size = "20";
    $req->setParamAeopOrderQuery(json_encode($param_aeop_order_query));
    $resp = $c->execute($req, $sessionKey);
    $res = json_encode((array)$resp);
    var_dump($resp);
    return json_decode($res, true);

}


require_once '../integration/ali_express/taobao/TopSdk.php';
define('APPKEY', '27862248');
define('SECRET', 'ca6916e55a087b3561b5077fc8b83ee6');
$credential = array('login'=>"NezabudkaND@yandex.ru","sessionKey"=>"50002701012q0OsaZ5izjnxB1c6971a8swh9hEtfmTvHKEuIYjVSokXFspWEUsi208y");
$shorten = formMasterList($credential);
//var_dump($shorten['logistics_amount']['cent']);
//var_dump($shorten['order_amount']['cent']);
var_dump($shorten);
