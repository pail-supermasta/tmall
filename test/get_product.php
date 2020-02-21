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
$shorten = product('4607947683912', '50002300e00k125e1091EPynqa94I0kqyewPdYEDR8rRXcGePIvgr1mGX4SJq7FiFGn');
//var_dump($shorten);
echo $shorten;








