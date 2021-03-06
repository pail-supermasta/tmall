<?php

error_reporting(E_ALL);
ini_set("display_errors", 1);
ini_set("error_log", "php-error.log");


// Список складов для расчёта остатков

define('APPKEY', '30833672');
define('SECRET', '1021396785b2eaa1497b7a58dddf19b3');

define('LOGINS', array(
    array(
        'name' => 'bestgoodsstore',
        'login' => 'bestgoodsstore@yandex.ru',
        'field_id' => '0bbcd3e6-81f4-11e9-9109-f8fc0004dec8',
        'sessionKey' => '50002301029r4LfaZzIzGMtdxRiWieuBmz1cGhQJ41a449a01ITXlFanTOWxJBdCZLQ5',
        'cpCode' => 'QXJCQk1QcjJKTkZDbHk4ZVZ4bW11cFQ2L2QreW1XT0lJd2ZlMnEvL2dFZC9NbG5CSklEV2tiY0cxNkRSMWlYcQ==',
        'cnId' => '4398985192396'

    ),
));


$stores = array(
    // 'f80cdf08-29a0-11e6-7a69-971100124ae8', // Склад-РЦ3
    // 'f257b41d-c2d9-11e7-6b01-4b1d00131678', // СКЛАД ХД
    '48de3b8e-8b84-11e9-9ff4-34e8001a4ea1',  // MP_NFF
);


// Telegram err logs integration
require_once realpath(dirname(__FILE__) . '/..') . '/class/telegram.php';

require_once realpath(dirname(__FILE__) . '/..') . '/ali_express/taobao/TopSdk.php';

require_once realpath(dirname(__FILE__) . '/..') . '/vendor/autoload.php';


use Avaks\MS\MSSync;
use Avaks\MS\OrderMS;

function getAllDeliveringMS($states)
{
    $regex = new \MongoDB\BSON\Regex('BESTGOODS');
    $collection = (new MSSync())->MSSync;
    $filter = [
        '_agent' => '1b33fbc1-5539-11e9-9ff4-315000060bc8',
        'deleted' => ['$exists' => false],
//        'description' => ['$regex'=> $regex]
    ];

    $filter['_state'] = $states;
    $productCursor = $collection->customerorder->find($filter)->toArray();
    return $productCursor;
}

function findOrderSessionKey($deliveringAliOrder)
{
    $sessionKey = '';

    if (preg_match('/Avax store/', $deliveringAliOrder['description'], $matches)) {

        $sessionKey = '50002500c15tBJCowQScZ6julhS1dea01bfctShXhETAmyVfKkQKwey1lvK3ryD7z1t';

    } elseif (preg_match('/BESTGOODS/', $deliveringAliOrder['description'], $matches)) {

        $sessionKey = '50002301029r4LfaZzIzGMtdxRiWieuBmz1cGhQJ41a449a01ITXlFanTOWxJBdCZLQ5';

    } elseif (preg_match('/Noerden/', $deliveringAliOrder['description'], $matches)) {

        $sessionKey = '50002700811duwgr7oCt2sx1edacab6BZ6kWiLtBtQeBGBQfNxYCKlunzemnpZpP21d';

    } elseif (preg_match('/Morphy Richards/', $deliveringAliOrder['description'], $matches)) {

        $sessionKey = '50002500501Vs151ac533BdqLFskhnQwtwheYk1CiSexTFfFAv6nWUefGArBboUuh8F';

    } elseif (preg_match('/iRobot/', $deliveringAliOrder['description'], $matches)) {

        $sessionKey = '50002500901sP1b7f673cFt7iCP3uQfb1JzgquDxxdddBqgtvwCkmwoWHooj7Cw4dCP';
    } elseif (preg_match('/orion/', $deliveringAliOrder['description'], $matches)) {

        $sessionKey = '50002201211qy8OzguEiR9T194d19ebvE7Girftw0dmHtGxmyX9d28OxEySXGK37wpOd';

    } elseif (preg_match('/novinkiooo/', $deliveringAliOrder['description'], $matches)) {

        $sessionKey = '50002500721djOAer7LBO3mQAZ5jVdhrE1730b9ebRsc7FeT4LrwHiIRmawEnCmZwzue';
    }

    return $sessionKey;
}

function aliexpress_trade_new_redefining_findorderbyid($orderName, $sessionKey)
{


    $c = new TopClient;
    $c->format = "json";
    $c->appkey = APPKEY;
    $c->secretKey = SECRET;
    $req = new AliexpressTradeNewRedefiningFindorderbyidRequest;
    $param1 = new AeopTpSingleOrderQuery;
    $param1->field_list = "1";
    $param1->order_id = $orderName;
    $param1->show_id = "1";
    $req->setParam1(json_encode($param1));
    $response = $c->execute($req, $sessionKey);

    if (!isset($response->target)) {
        return false;
    } else {

        $res = json_encode($response, JSON_UNESCAPED_UNICODE);
        return $res;
    }


}

$orderMS = new OrderMS();


$deliveringAliOrders = getAllDeliveringMS('327c03c6-75c5-11e5-7a40-e89700139938');

foreach ($deliveringAliOrders as $deliveringAliOrder) {
    $sessionKey = findOrderSessionKey($deliveringAliOrder);
    $orderDetailsResponse = aliexpress_trade_new_redefining_findorderbyid($deliveringAliOrder['name'], $sessionKey);
    $orderDetailsResponse = json_decode($orderDetailsResponse, true);
    if (isset($orderDetailsResponse['target']['order_end_reason']) && $orderDetailsResponse['target']['order_end_reason'] == 'buyer_accept_goods') {
        $orderMS->id = $deliveringAliOrder['id'];
        $orderMS->state = '8beb27b2-6088-11e7-7a6c-d2a9003b81a5';
        $orderMS->setStatus();
        error_log(date("Y-m-d H:i:s", strtotime(gmdate("Y-m-d H:i:s")) + 3 * 60 * 60) . " " . $deliveringAliOrder['name'] . PHP_EOL, 3, "list_delivered.log");

    }
}
























