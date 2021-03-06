<?php

// display results as json
header('Content-Type: application/json');


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set("error_log", realpath(dirname(__FILE__) . '/..') . "/php-error.log");

define('MS_HOST', 'avaks.org');
define('MS_USER', 'avaks');
define('MS_PASS', 'SbTZN8L9fCpVDxtc');
define('MS_DB', 'avaks');

/*define('APPKEY', '30833672');
define('SECRET', '1021396785b2eaa1497b7a58dddf19b3');*/

define('LOGINS', array(
    array(
        'appkey' => '32817975', //aliexpr app key orion
        'secret' => 'fc3e140009f59832442d5c195c807fc0', //aliexpr app secret orion
        'name' => 'bestgoodsstore',
        'login' => 'bestgoodsstore@yandex.ru',
        'field_id' => '0bbcd3e6-81f4-11e9-9109-f8fc0004dec8',
        'sessionKey' => '50002301029r4LfaZzIzGMtdxRiWieuBmz1cGhQJ41a449a01ITXlFanTOWxJBdCZLQ5',
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
    ),
    array(
        'appkey' => '32817975', //aliexpr app key orion
        'secret' => 'fc3e140009f59832442d5c195c807fc0', //aliexpr app secret orion
        'name' => 'novinkiooo',
        'login' => 'novinkiooo@yandex.ru',
        'field_id' => 'e8a40577-77b9-11e9-912f-f3d40003d45d',
        'sessionKey' => '50002500721djOAer7LBO3mQAZ5jVdhrE1730b9ebRsc7FeT4LrwHiIRmawEnCmZwzue',
        'cpCode' => 'OTQwTzB2T1U3N1Nza0Y3OVRKMHZyVWtPL0RFRjJHczBqUHBDRHBqK05LVXdBc1pJRkk0THo1YUVLR21PNE5IZQ==',
        'cnId' => '4398985964371',
    )
));

define('ID_REGEXP', '/[0-9a-z]{8}-[0-9a-z]{4}-[0-9a-z]{4}-[0-9a-z]{4}-[0-9a-z]{12}/'); // ?????????????????? ?????? UUID

/*  INCLUDES    */

// Error handlers
require_once realpath(dirname(__FILE__) . '/..') . '/class/error.php';

// Telegram err logs integration
require_once realpath(dirname(__FILE__) . '/..') . '/class/telegram.php';

// SQL get track number for orden name from MS
require_once realpath(dirname(__FILE__) . '/..') . '/moi_sklad/sql_requests/OrderDetails.php';
require_once realpath(dirname(__FILE__) . '/..') . '/moi_sklad/ms_get_orders_dynamic.php';

require_once 'taobao/TopSdk.php';
//require_once 'cainiao.php';
require_once realpath(dirname(__FILE__) . '/..') . '/vendor/autoload.php';


use Avaks\MS\OrderMS;


/*Search for an error*/
function strpos_recursive($haystack, $needle, $offset = 0, &$results = array())
{
    $offset = strpos($haystack, $needle, $offset);
    if ($offset === false) {
        return $results;
    } else {
        $results[] = $offset;
        return strpos_recursive($haystack, $needle, ($offset + 1), $results);
    }
}


function setNewPositionPrice($order, $credential)
{
    $sessionKey = $credential['sessionKey'];
    $appkey = $credential['appkey'];
    $secret = $credential['secret'];

    /*get order details from Aliexp*/

    $findorderbyidRes = findorderbyid($order, $sessionKey, $appkey, $secret);


    /*get order ?????????? in MS*/

    $link = MS_PATH . "/entity/customerorder/?filter=name=" . $order . "&expand=positions";


    $res = json_decode(curlMS($link), true)['rows'][0];

    $orderMSSum = $res['sum'] / 100;

    $orderMS = new OrderMS($res['id']);


    $pay_amount_by_settlement_cur = $findorderbyidRes['pay_amount_by_settlement_cur'];
    $logistics_amount = $findorderbyidRes['logistics_amount']['cent'] ?? 0;
    $diff = $orderMSSum - $pay_amount_by_settlement_cur + round($logistics_amount / 100);


    $products = $res['positions']['rows'];
    foreach ($products as $product) {
        /*?????????? ???????? ?????????????????? ??????????????????*/
        $oldPosTot = $product["quantity"] * $product["price"] / 100;
        $newPosTot = $oldPosTot - $diff * ($oldPosTot / $orderMSSum);
        $newPrice = 100 * $newPosTot / $product["quantity"];

        /*set new prices in MS*/

        $postdata = '{
                  "price": ' . $newPrice . '
                }';


        $setNewPositionPriceResp = $orderMS->updatePositions($postdata, $product['id']);
        /*check if no Errors from MS or check again otherwise send TG message*/
        if (strpos($setNewPositionPriceResp, '??????????????????-????????????') > 0 || $setNewPositionPriceResp == '') {
            telegram("setNewPositionPrice error found " . $order, '-320614744');
            error_log(date("Y-m-d H:i:s", strtotime(gmdate("Y-m-d H:i:s")) + 3 * 60 * 60) . $setNewPositionPriceResp . " " . $order . PHP_EOL, 3, "setNewPositionPriceResp.log");
        }
    }

    $productsAli = $findorderbyidRes['child_order_list']['global_aeop_tp_child_order_dto'];
    $affiliateFeeSum = 0;
    $affiliateAmount = 0;

    foreach ($productsAli as $productAli) {
        if (isset($productAli['afflicate_fee_rate'])) {
            $affiliateFeeSum += $productAli['afflicate_fee_rate'];
        }
    }

    /*Affiliate fee*/
    $affiliateFee = $affiliateFeeSum / sizeof($productsAli);
    $affiliateAmount = ($pay_amount_by_settlement_cur - $logistics_amount / 100) * $affiliateFee;

    foreach ($res['attributes'] as $attribute) {
        if ($attribute['id'] == '535dd809-1db1-11ea-0a80-04c00009d6bf') {
            $dshSum = $attribute['value'] + $affiliateAmount;
            break;
        }
    }

    /*update comment*/
    $newLines = " Affiliate fee: $affiliateAmount. ?????????? ???????????? ?????? ????????????: $diff ?????????? ????????: $orderMSSum, ?????????? ???????????????? $pay_amount_by_settlement_cur, ?????????? ???????????????? " . $logistics_amount / 100;
    $oldDescription = $res['description'];
    /*?????????????? ?????????????? ??????????????*/
    $oldDescription = str_replace('"', '', $oldDescription);
    /*?????????????? ?????????? ????????????*/
    $oldDescription = preg_replace('/\s+/', ' ', trim($oldDescription));

    $postdata2 = '{
                  "description": "' . $oldDescription . $newLines . '",
                  "attributes": [{
                        "id": "535dd809-1db1-11ea-0a80-04c00009d6bf",
                        "value": ' . $dshSum . '
                  }]
                }';
    $updateOrderResp = $orderMS->updateOrder($postdata2);
    if (strpos($updateOrderResp, '??????????????????-????????????') > 0 || $updateOrderResp == '') {
        telegram("updateOrderResp error found " . $order, '-320614744');
        error_log(date("Y-m-d H:i:s", strtotime(gmdate("Y-m-d H:i:s")) + 3 * 60 * 60) . $updateOrderResp . " " . $order . PHP_EOL, 3, "updateOrderResp.log");

    }


}


function setTrackToTmall($order, $payTime, $credential)
{

    $sessionKey = $credential['sessionKey'];
    $appkey = $credential['appkey'];
    $secret = $credential['secret'];

    /*check if in MS state is ???????????????????????? and has a track number*/
    $orderMSTemp = new OrderMS(null, strval($order));
    $delivery = $orderMSTemp->getOrderTrackNew();
    /*??????????????????????????, ???? ????????????, ????????????????????????*/
    /*$statesToShip = array("8beb227b-6088-11e7-7a6c-d2a9003b81a3",
        "8beb25ab-6088-11e7-7a6c-d2a9003b81a4",
        "327c03c6-75c5-11e5-7a40-e89700139938");*/


//    if (in_array($delivery['state'], $statesToShip) == 1 && $delivery['track'] != false) {
    if ($delivery['track'] != false) {
        $trackId = $delivery['track'];
        $agentId = $delivery['agent'];
        /*set track number in Tmall*/

        $serviceName = "";
        switch ($agentId) {
            case "CDEK":
                $trackingWebsite = "https://cdek.ru/track.html?order_id=$trackId";
                $serviceName = "OTHER_RU_CITY_RUB";
                break;
            case "IML":
                $trackingWebsite = "https://iml.ru/status/";
                $serviceName = "OTHER_RU_CITY_RUB";
                break;

        }




        if ($serviceName!==""){
            $c = new TopClient;
            $c->appkey = $appkey;
            $c->secretKey = $secret;
            $req = new AliexpressSolutionOrderFulfillRequest;
            $req->setServiceName($serviceName);
            $req->setTrackingWebsite("$trackingWebsite");
            $req->setOutRef("$order");
            $req->setSendType("all");
            $req->setLogisticsNo("$trackId");
            $resp = $c->execute($req, $sessionKey);
            var_dump($resp);
        }

    }


}

function formMasterList($credential)
{


    $buyer_login_id = $credential['login'];
    $sessionKey = $credential['sessionKey'];
    $appkey = $credential['appkey'];
    $secret = $credential['secret'];


    /*additional - 3 hours for RU shift*/
    $offset = 72 * 60 * 60;
    $create_day = date("Y-m-d H:i:s", strtotime(gmdate("Y-m-d H:i:s")) - $offset);

    $c = new TopClient;
    $c->format = "json";
    $c->appkey = $appkey;
    $c->secretKey = $secret;
    $req = new AliexpressTradeSellerOrderlistGetRequest;
    $param_aeop_order_query = new AeopOrderQuery;
    $param_aeop_order_query->buyer_login_id = $buyer_login_id;
    $param_aeop_order_query->create_date_start = $create_day;
    $param_aeop_order_query->current_page = "1";
    $param_aeop_order_query->order_status_list = array('WAIT_SELLER_SEND_GOODS');
    $param_aeop_order_query->page_size = "20";
    $req->setParamAeopOrderQuery(json_encode($param_aeop_order_query));
    $resp = $c->execute($req, $sessionKey);

    $final = $resp->result->target_list->aeop_order_item_dto ?? "";


    $orderList = array();

    if ($final != "") {
        $res = json_encode((array)$final);
        $shortener = json_decode($res, true);
        foreach ($shortener as $shorty) {
            if ($shorty['order_status'] == 'WAIT_SELLER_SEND_GOODS') {
                $orderList[$shorty['order_id']] = $shorty['gmt_pay_time'];

                /*check if paid and no track number*/
//                checkTimeFromPaid($shorty['order_id'], $shorty['gmt_pay_time'], $credential);

                /*check if  has track num*/
                setTrackToTmall($shorty['order_id'], $shorty['gmt_pay_time'], $credential);


            }
        }
    }

    return $orderList;
}


foreach (LOGINS as $credential) {
    formMasterList($credential);
}






























