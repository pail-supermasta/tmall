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
    $req = new AliexpressPostproductRedefiningFindaeproductbyidRequest;
    $req->setProductId($post_data);
    $resp = $c->execute($req, $sessionKey);
    $res = json_encode((array)$resp,JSON_UNESCAPED_UNICODE);

//    return json_decode($res, true);
    return $res;
}


//$shorten = product('33049309048', '50002701012q0OsaZ5izjnxB1c6971a8swh9hEtfmTvHKEuIYjVSokXFspWEUsi208y');
$shorten = product('4000236211697', '50002301537b3HwdiqAxRjekju8LsvFIFOnVHqTAiY8rpjwSW11d54902lPHBjVb1lb');
//var_dump($shorten);
echo $shorten;








