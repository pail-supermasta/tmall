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


//$shorten = listcategory('', '50002301422cdaiudiQfUvkfkfueltWFHi137e3f5bQGZdRtFEwBpxDxPrTG2jMrE6H');
//var_dump($shorten);

$newCategRus = array(
    "Пылесосы" => array("Беспроводные пылесосы",
        "Роботы-мойщики окон",
        "Роботы-полотеры",
        "Роботы-пылесосы",
        "Запчасти и Аксессуары",
    ),
    "Детская продукция" => array("Детские матрасики",
        "Игрушки",
    ),
    "3D печать" => array("3D ручки",
        "Чернила для 3D ручек",
        "Трафареты для 3D ручек",
    ),
    "Умные устройства" => array("Умные весы",
        "Мониторы сердечной активности",
    ),
    "Электрические велосипеды" => array()
);

function createcategory($name, $parentkey, $sessionKey)
{
    $c = new TopClient;
    $c->appkey = APPKEY;
    $c->secretKey = SECRET;
    $req = new AliexpressPostproductRedefiningCreateproductgroupRequest;
    $req->setName($name);
    $req->setParentId($parentkey);
    $resp = $c->execute($req, $sessionKey);
    $res = json_encode((array)$resp);
    return $res;
}

foreach ($newCategRus as $parent=>$childs){
    if(sizeof($childs)>0){
        $parentID = '0';
    }
    $shorten = createcategory($parent,$parentID, '50002301422cdaiudiQfUvkfkfueltWFHi137e3f5bQGZdRtFEwBpxDxPrTG2jMrE6H');
    $shorten = json_decode($shorten,true);
    if (isset($shorten["result"]["target"]) && sizeof($childs)>0){
        $parentID = $shorten["result"]["target"];
        foreach ($childs as $child){
            $shorten = createcategory($child,$parentID, '50002301422cdaiudiQfUvkfkfueltWFHi137e3f5bQGZdRtFEwBpxDxPrTG2jMrE6H');
        }
    }
    die();

}



/*
function getcategory($post_data, $sessionKey)
{
    $c = new TopClient;
    $c->appkey = APPKEY;
    $c->secretKey = SECRET;
    $req = new AliexpressSolutionSellerCategoryTreeQueryRequest;
    $req->setCategoryId("0");
    $req->setFilterNoPermission("true");
    $resp = $c->execute($req, $sessionKey);
    return $resp;
}

$shorten = getcategory('516652310', '50002301422cdaiudiQfUvkfkfueltWFHi137e3f5bQGZdRtFEwBpxDxPrTG2jMrE6H');
var_dump($shorten);*/








