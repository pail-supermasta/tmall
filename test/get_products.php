<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 07.10.2019
 * Time: 14:23
 */


require_once '../integration/ali_express/taobao/TopSdk.php';
define('APPKEY', '27862248');
define('SECRET', 'ca6916e55a087b3561b5077fc8b83ee6');


function product($post_data, $sessionKey)
{


    $c = new TopClient;
    $c->appkey = APPKEY;
    $c->secretKey = SECRET;
    $req = new AliexpressSolutionProductListGetRequest;
    $aeop_a_e_product_list_query = new ItemListQuery;
    $aeop_a_e_product_list_query->current_page="1";
//    $aeop_a_e_product_list_query->excepted_product_ids="[32962333569,32813963253]";
//    $aeop_a_e_product_list_query->off_line_time="7";
//    $aeop_a_e_product_list_query->owner_member_id="aliqatest01";
    $aeop_a_e_product_list_query->page_size="30";
//    $aeop_a_e_product_list_query->product_id="123";
    $aeop_a_e_product_list_query->product_status_type="onSelling";
//    $aeop_a_e_product_list_query->subject="knew odd";
//    $aeop_a_e_product_list_query->ws_display="expire_offline";
//    $aeop_a_e_product_list_query->have_national_quote="n";
//    $aeop_a_e_product_list_query->group_id="1234";
//    $aeop_a_e_product_list_query->gmt_create_start="2012-01-01 12:13:14";
//    $aeop_a_e_product_list_query->gmt_create_end="2012-01-01 12:13:14";
//    $aeop_a_e_product_list_query->gmt_modified_start="2012-01-01 12:13:14";
//    $aeop_a_e_product_list_query->gmt_modified_end="2012-01-01 12:13:14";
//    $aeop_a_e_product_list_query->sku_code="123ABC";
    $req->setAeopAEProductListQuery(json_encode($aeop_a_e_product_list_query));
    $res= $c->execute($req, $sessionKey);
    var_dump($res);
    die();

    /*$c = new TopClient;
    $c->appkey = APPKEY;
    $c->secretKey = SECRET;
    $req = new AliexpressPostproductRedefiningFindaeproductbyidRequest;
    $req->setProductId($post_data);
    $resp = $c->execute($req, $sessionKey);
    $res = json_encode((array)$resp,JSON_UNESCAPED_UNICODE);*/

//    return json_decode($res, true);
    return $res;
}


//$shorten = product('33049309048', '50002701012q0OsaZ5izjnxB1c6971a8swh9hEtfmTvHKEuIYjVSokXFspWEUsi208y');
$shorten = product('4607947683912', '50002300d289mlqwnqAebm1lqueSpe9ECuAKVtDg1b6f0174jvgyezVnDsmJ4OkbLKm');
//var_dump($shorten);
echo $shorten;








