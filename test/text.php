<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 07.10.2019
 * Time: 14:23
 */


require_once 'integration/ali_express/taobao/TopSdk.php';
define('APPKEY', '27862248');
define('SECRET', 'ca6916e55a087b3561b5077fc8b83ee6');


function bundle($post_data, $sessionKey)
{


    $c = new TopClient;
    $c->format = "json";
    $c->appkey = APPKEY;
    $c->secretKey = SECRET;
    $req = new AliexpressOfferRedefiningQuerybundleRequest;
    $param_aeop_offer_bundle_query_condition = new AeopOfferBundleQueryCondition;
    $param_aeop_offer_bundle_query_condition->current_page = "1";
    $param_aeop_offer_bundle_query_condition->item_id = "33022142079";
//    $param_aeop_offer_bundle_query_condition->item_subject = "";
    $param_aeop_offer_bundle_query_condition->page_size = "10";
    $req->setParamAeopOfferBundleQueryCondition(json_encode($param_aeop_offer_bundle_query_condition));
    $resp = $c->execute($req, $sessionKey);

    var_dump($param_aeop_offer_bundle_query_condition);
//    return json_decode($resp,true);
    return $resp;
}


$shorten = bundle('33022142079', '50002700f07CsXpqaf1167cf3bdl0ipRaztFcFeR5MtYHGEvJ1IQXHD3CpkVzlo6zzy');
var_dump($shorten);


function bundlebyid($post_data, $sessionKey)
{


    $c = new TopClient;
    $c->format = "json";
    $c->appkey = APPKEY;
    $c->secretKey = SECRET;
    $req = new AliexpressOfferRedefiningFindbundlebyidRequest;
    $req->setBundleId("10001");
    $resp = $c->execute($req, $sessionKey);
    var_dump($req);


//    return json_decode($resp,true);
    return $resp;
}


//$shorten = bundlebyid('10001', '50002700f07CsXpqaf1167cf3bdl0ipRaztFcFeR5MtYHGEvJ1IQXHD3CpkVzlo6zzy');
//var_dump($shorten);





