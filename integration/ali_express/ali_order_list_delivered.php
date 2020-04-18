<?php

error_reporting(E_ALL);
ini_set("display_errors", 1);
ini_set("error_log", "php-error.log");


// Список складов для расчёта остатков

define('APPKEY', '27862248');
define('SECRET', 'ca6916e55a087b3561b5077fc8b83ee6');

define('LOGINS', array(
    array(
        'name' => 'Незабудка MR',
        'login' => 'NezabudkaMR@yandex.ru',
        'field_id' => '0bbcd991-81f4-11e9-9109-f8fc0004dec9',
        'sessionKey' => '50002500428yXPcbqwfU6Lmo2txf83nTdKufWRlA15757c75Bgs6PSvcIUUwtbSdEo3',
        'cpCode' => 'UTV0a1NLakt5dE9DdzZOdEt1elhnblRnMURQaExvS0w4RVZEVHMyM2o2eTRqUjdiOEdxalpTVjhRN0ZBQldVZA==',
        'cnId' => '4398983084403'
    ),
    array(
        'name' => 'bestgoodsstore',
        'login' => 'bestgoodsstore@yandex.ru',
        'field_id' => '0bbcd3e6-81f4-11e9-9109-f8fc0004dec8',
        'sessionKey' => '50002301419cdaiudiQfUvkfkfueltW1bd07081FHiQGZdRtFEwBpxDxPrTG2jMrE6H',
        'cpCode' => 'QXJCQk1QcjJKTkZDbHk4ZVZ4bW11cFQ2L2QreW1XT0lJd2ZlMnEvL2dFZC9NbG5CSklEV2tiY0cxNkRSMWlYcQ==',
        'cnId' => '4398985192396'

    ),
    array(
        'name' => 'Новинки',
        'login' => 'novinkiooo@yandex.ru',
        'field_id' => 'e8a40577-77b9-11e9-912f-f3d40003d45d',
        'sessionKey' => '50003501012q0OsaZcIwdpta182c9ed30oFZdEu4qVXjhkvjakxWAd4HQ7yEr5SZx1z',
        'cpCode' => 'OTQwTzB2T1U3N1Nza0Y3OVRKMHZyVWtPL0RFRjJHczBqUHBDRHBqK05LVXdBc1pJRkk0THo1YUVLR21PNE5IZQ==',
        'cnId' => '4398985964371'
    ),
    array(
        'name' => 'Незабудка iRobot',
        'login' => 'NezabudkaiRobot@yandex.ru',
        'field_id' => '0bbcde02-81f4-11e9-9109-f8fc0004deca',
        'sessionKey' => '50002500403yXPc1faa68e8bqNFyEIFqyQvdh3JzliwbSpFffdP8tU1DJbFGQte8yE2',
        'cpCode' => 'czJBM3dFNm9aQ0RuSnhnY0tEK2p2a3g1cEI5aFYwSGw1TlpxTVAyUE1CYk1iYkRCTU1tWENocFo4alU3aFdmUg==',
        'cnId' => '4398985334183'
    ),
    array(
        'name' => 'Незабудка ND',
        'login' => 'NezabudkaND@yandex.ru',
        'field_id' => '0bbce15c-81f4-11e9-9109-f8fc0004decb',
        'sessionKey' => '50002700524VsBdqLktCoFvXnxeB4JxEjRbW111b877fTD7DDviKW1DmhKcqJnlQq9B',
        'cpCode' => 'V1ZDUlZnY09vbHoyQTFpNEZEUElkcGlmUE43Z1hYZEdoVEZwM2huTDlWeWVKUHdIUmY4QmFWV1FOdXVCT3JQeg==',
        'cnId' => '4398983195649'
    )
));


$stores = array(
    // 'f80cdf08-29a0-11e6-7a69-971100124ae8', // Склад-РЦ3
    // 'f257b41d-c2d9-11e7-6b01-4b1d00131678', // СКЛАД ХД
    '48de3b8e-8b84-11e9-9ff4-34e8001a4ea1',  // MP_NFF
);


// Telegram err logs integration
require_once '../class/telegram.php';

require_once '../ali_express/taobao/TopSdk.php';

require_once '../vendor/autoload.php';


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

        $sessionKey = '50003501012q0OsaZcIwdpta182c9ed30oFZdEu4qVXjhkvjakxWAd4HQ7yEr5SZx1z';

    } elseif (preg_match('/BESTGOODS/', $deliveringAliOrder['description'], $matches)) {

        $sessionKey = '50002301419cdaiudiQfUvkfkfueltW1bd07081FHiQGZdRtFEwBpxDxPrTG2jMrE6H';

    } elseif (preg_match('/Noerden/', $deliveringAliOrder['description'], $matches)) {

        $sessionKey = '50002700524VsBdqLktCoFvXnxeB4JxEjRbW111b877fTD7DDviKW1DmhKcqJnlQq9B';

    } elseif (preg_match('/Morphy Richards/', $deliveringAliOrder['description'], $matches)) {

        $sessionKey = '50002500428yXPcbqwfU6Lmo2txf83nTdKufWRlA15757c75Bgs6PSvcIUUwtbSdEo3';

    } elseif (preg_match('/iRobot/', $deliveringAliOrder['description'], $matches)) {

        $sessionKey = '50002500403yXPc1faa68e8bqNFyEIFqyQvdh3JzliwbSpFffdP8tU1DJbFGQte8yE2';
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
























