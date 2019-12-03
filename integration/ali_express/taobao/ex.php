<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 02.09.2019
 * Time: 17:17
 */

require_once 'TopSdk.php';

$buyer_login_id = 'NezabudkaMR@yandex.ru';
$appkey = '27862248';
$secret = 'ca6916e55a087b3561b5077fc8b83ee6';
$sessionKey = '50002500a43NiS9iAPxQpDBzm0knPezOd9ggUgtVTiigyHxdTjKsu6Q1eb66647ZfVv';


$c = new TopClient;
$c->format = "json";
$c->appkey = $appkey;
$c->secretKey = $secret;
$req = new AliexpressTradeSellerOrderlistGetRequest;
$param_aeop_order_query = new AeopOrderQuery;
$param_aeop_order_query->buyer_login_id = $buyer_login_id;
//$param_aeop_order_query->create_date_end = "2019-05-05 12:12:12";
$param_aeop_order_query->create_date_start = "2019-08-20 12:48:14";
$param_aeop_order_query->current_page = "1";
//$param_aeop_order_query->modified_date_end="2017-10-12 12:12:12";
//$param_aeop_order_query->modified_date_start="2017-10-12 12:12:12";
//$param_aeop_order_query->order_status="SELLER_PART_SEND_GOODS";
//$param_aeop_order_query->order_status="WAIT_SELLER_SEND_GOODS";
//$param_aeop_order_query->order_status_list="SELLER_PART_SEND_GOODS";
$param_aeop_order_query->order_status_list = "FINISH";
$param_aeop_order_query->page_size = "20";
$req->setParamAeopOrderQuery(json_encode($param_aeop_order_query));
$resp = $c->execute($req, $sessionKey);

$final = $resp->result->target_list->aeop_order_item_dto;
foreach ($final as $order) {
    print_r($order);
}

