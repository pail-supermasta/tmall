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


function listcategory($post_data, $sessionKey)
{


    $c = new TopClient;
    $c->appkey = APPKEY;
    $c->secretKey = SECRET;
    $req = new AliexpressProductProductgroupsGetRequest;
    $resp = $c->execute($req, $sessionKey);

//    return json_decode($resp,true);
    return $resp;
}

function createcategory($post_data, $sessionKey)
{
    $c = new TopClient;
    $c->appkey = APPKEY;
    $c->secretKey = SECRET;
    $req = new AliexpressPostproductRedefiningCreateproductgroupRequest;
    $req->setName("Категория");
    $req->setParentId("0");
    $resp = $c->execute($req, $sessionKey);
//    return json_decode($resp,true);
    return $resp;
}


//$shorten = listcategory('5090301', '50002301422cdaiudiQfUvkfkfueltWFHi137e3f5bQGZdRtFEwBpxDxPrTG2jMrE6H');
//
//var_dump($shorten);


function getcategory($post_data, $sessionKey)
{
    $c = new TopClient;
    $c->appkey = APPKEY;
    $c->secretKey = SECRET;
    $req = new AliexpressSolutionSellerCategoryTreeQueryRequest;
    $req->setCategoryId("0");
    $req->setFilterNoPermission("true");
    $resp = $c->execute($req, $sessionKey);
//    return json_decode($resp,true);
    return $resp;
}

$shorten = getcategory('5090301', '50002301422cdaiudiQfUvkfkfueltWFHi137e3f5bQGZdRtFEwBpxDxPrTG2jMrE6H');
var_dump($shorten);








