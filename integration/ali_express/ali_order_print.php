<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 23.09.2019
 * Time: 11:42
 */


ini_set('display_errors', 1);

header('Content-Type: application/json');


use Avaks\MS\Orders;
use Avaks\MS\OrderMS;


require_once realpath(dirname(__FILE__) . '/..') . '/vendor/autoload.php';
require_once 'ali_order_details_dynamic.php';
require_once 'taobao/TopSdk.php';

//error_log(date("Y-m-d H:i:s", strtotime(gmdate("Y-m-d H:i:s")) + 3 * 60 * 60) . "1" . PHP_EOL, 3, "cronrunend.log");


/*define('APPKEY', '30833672');
define('SECRET', '1021396785b2eaa1497b7a58dddf19b3');*/
define('LOGINS', array(
    array(
        'appkey' => '30833672',
        'secret' => '1021396785b2eaa1497b7a58dddf19b3',
        'name' => 'bestgoodsstore',
        'login' => 'bestgoodsstore@yandex.ru',
        'field_id' => '0bbcd3e6-81f4-11e9-9109-f8fc0004dec8',
        'sessionKey' => '50002300413yAdDbqygrAkmv21cf1a94bsqga2hwEpqARrGXkfThpxxhkZxBBRHfZ7x',
        'cpCode' => 'QXJCQk1QcjJKTkZDbHk4ZVZ4bW11cFQ2L2QreW1XT0lJd2ZlMnEvL2dFZC9NbG5CSklEV2tiY0cxNkRSMWlYcQ==',
        'cnId' => '4398985192396'
    ),
    array(
        'appkey' => '32817975', //aliexpr app key orion
        'secret' => 'fc3e140009f59832442d5c195c807fc0', //aliexpr app secret orion
        'name' => 'orion',
        'login' => 'orionstore360@gmail.com',
        'field_id' => '0bbcde02-81f4-11e9-9109-f8fc0004deca', // get once from product field id MS
        'sessionKey' => '50002201211qy8OzguEiR9T194d19ebvE7Girftw0dmHtGxmyX9d28OxEySXGK37wpOd', // get after auth for ali api app
        'cpCode' => '***', // get once
        'cnId' => '****' // get once
    )
));


$orders = new Orders();
$ordersNoSticker = $orders->getOrdersNoSticker();


function printCainiaoSticker(OrderMS $orderMS, $sessionKey,$appkey,$secret)
{


    $customs = new \Avaks\Customs();
    $findorderbyidRes = findorderbyid($orderMS->name, $sessionKey,$appkey,$secret);


    $productShortener = $findorderbyidRes['child_order_ext_info_list']['global_aeop_tp_order_product_info_dto'];
    $productDescriptions = array();
    foreach ((array)$productShortener as $product) {
        $productDescriptions[] = $product['product_name'];
    }


//    $mailNo = 'AEWH000657988RU4';
//    $mailNo = 'AEWH000' . $orderMS->trackNum . 'RU4';
    $mailNo = $orderMS->trackNum;

    $address = $findorderbyidRes['receipt_address'];
    $country = $address['country'] == 'RU' ? 'РФ' : $address['country'];
    $address2 = isset($address['address2']) ? $address['address2'] : '';
    $fullAddress = $address['zip'] . ", " . $country . ", " . $address['province'] . ", " . $address['city'] . ", " . $address['detail_address'] . ", " . $address2;
    $receiverName = $address['contact_person'];
    $receiverPhone = $address['mobile_no'] ?? '';
    $otgruzkaDate = date('Y-m-d', strtotime($findorderbyidRes['gmt_create']));
//    $lpNum = 'LP00141041126618';
    $lpNum = 'LP00' . $orderMS->lpNumber;


    $toPrint = array('productDescriptions' => $productDescriptions,
        'fullAddress' => $fullAddress,
        'receiverName' => $receiverName,
        'receiverPhone' => $receiverPhone,
        'mailNo' => $mailNo,
        'otgruzkaDate' => $otgruzkaDate,
        'LPNum' => $lpNum,
        'AEOrderId' => $orderMS->name);

    $pdfCode = $customs->generateCainiaoSticker($toPrint);

    $content = base64_encode($pdfCode);
    $attribute['id'] = 'b8a8f6d6-5782-11e8-9ff4-34e800181bf6';
    $attribute['file']['filename'] = "Маркировка $orderMS->name.pdf";
    $attribute['file']['content'] = $content;
    $put_data['attributes'][] = $attribute;

    $final = json_encode($put_data);

    return $orderMS->setSticker($final);


}


function getOrderShop($attributes)
{
    $sessionKey = '';
    $appkey = '';
    $secret = '';
    switch (true) {
        case  stripos($attributes, "BESTGOODS (ID 5041091)") !== false :
            $sessionKey = "50002300413yAdDbqygrAkmv21cf1a94bsqga2hwEpqARrGXkfThpxxhkZxBBRHfZ7x";
            $appkey = '30833672';
            $secret = '1021396785b2eaa1497b7a58dddf19b3';
            break;
        case  stripos($attributes, "orion (ID 911725024)") !== false :
            $sessionKey = "50002201211qy8OzguEiR9T194d19ebvE7Girftw0dmHtGxmyX9d28OxEySXGK37wpOd";
            $appkey = '32817975';
            $secret = 'fc3e140009f59832442d5c195c807fc0';
            break;
    }
    return [$sessionKey, $appkey, $secret];
}

foreach ($ordersNoSticker as $orderNoSticker) {

    $orderMS = new OrderMS($orderNoSticker['id'], $orderNoSticker['name']);
    $orderMS->lpNumber = $orderNoSticker['externalCode'];
    $orderMS->trackNum =  $orderNoSticker['trackNum'];
    $getOrderShopArr = getOrderShop($orderNoSticker['description']);
    $sessionKey=$getOrderShopArr[0];
    $appkey =$getOrderShopArr[1];
    $secret =$getOrderShopArr[2];

    var_dump(printCainiaoSticker($orderMS, $sessionKey,$appkey,$secret));

}


