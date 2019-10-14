<?php

// display results as json
header('Content-Type: application/json');


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set("error_log", "../php-error.log");

define('MS_HOST', 'avaks.org');
define('MS_USER', 'avaks');
define('MS_PASS', 'SbTZN8L9fCpVDxtc');
define('MS_DB', 'avaks');

define('APPKEY', '27862248');
define('SECRET', 'ca6916e55a087b3561b5077fc8b83ee6');

define('LOGINS', array(
    array(
        'name' => 'Незабудка MR',
        'login' => 'NezabudkaMR@yandex.ru',
        'field_id' => '0bbcd991-81f4-11e9-9109-f8fc0004dec9',
        'sessionKey' => '50002500e10kEPynqBacfX146882beFiwgzuCbjAqgpxYFoHtmygVTBcZzz4YHQguxt',
        'cpCode' => 'UTV0a1NLakt5dE9DdzZOdEt1elhnblRnMURQaExvS0w4RVZEVHMyM2o2eTRqUjdiOEdxalpTVjhRN0ZBQldVZA==',
        'cnId' => '4398983084403'
    ),
    array(
        'name' => 'bestgoodsstore',
        'login' => 'bestgoodsstore@yandex.ru',
        'field_id' => '0bbcd3e6-81f4-11e9-9109-f8fc0004dec8',
        'sessionKey' => '50002301422cdaiudiQfUvkfkfueltWFHi137e3f5bQGZdRtFEwBpxDxPrTG2jMrE6H',
        'cpCode' => 'QXJCQk1QcjJKTkZDbHk4ZVZ4bW11cFQ2L2QreW1XT0lJd2ZlMnEvL2dFZC9NbG5CSklEV2tiY0cxNkRSMWlYcQ==',
        'cnId' => '4398985192396'

    ),
    array(
        'name' => 'Новинки',
        'login' => 'novinkiooo@yandex.ru',
        'field_id' => 'e8a40577-77b9-11e9-912f-f3d40003d45d',
        'sessionKey' => '50002501218puBbzgXFkqhvplWEdrh1c808e85pszGJkLnXgVzGGx9wxCcCqsvPj6As',
        'cpCode' => 'OTQwTzB2T1U3N1Nza0Y3OVRKMHZyVWtPL0RFRjJHczBqUHBDRHBqK05LVXdBc1pJRkk0THo1YUVLR21PNE5IZQ==',
        'cnId' => '4398985964371'
    ),
    array(
        'name' => 'Незабудка iRobot',
        'login' => 'NezabudkaiRobot@yandex.ru',
        'field_id' => '0bbcde02-81f4-11e9-9109-f8fc0004deca',
        'sessionKey' => '50002500345b04qabswlyhnCqZstidcl2dorAsRlaeAuCNzUGdZtBBR6h1c2a3209li',
        'cpCode' => 'czJBM3dFNm9aQ0RuSnhnY0tEK2p2a3g1cEI5aFYwSGw1TlpxTVAyUE1CYk1iYkRCTU1tWENocFo4alU3aFdmUg==',
        'cnId' => '4398985334183'
    ),
    array(
        'name' => 'Незабудка ND',
        'login' => 'NezabudkaND@yandex.ru',
        'field_id' => '0bbce15c-81f4-11e9-9109-f8fc0004decb',
        'sessionKey' => '50002700f07CsXpqaf1167cf3bdl0ipRaztFcFeR5MtYHGEvJ1IQXHD3CpkVzlo6zzy',
        'cpCode' => 'V1ZDUlZnY09vbHoyQTFpNEZEUElkcGlmUE43Z1hYZEdoVEZwM2huTDlWeWVKUHdIUmY4QmFWV1FOdXVCT3JQeg==',
        'cnId' => '4398983195649'
    )
));

define('ID_REGEXP', '/[0-9a-z]{8}-[0-9a-z]{4}-[0-9a-z]{4}-[0-9a-z]{4}-[0-9a-z]{12}/'); // Регулярка для UUID

/*  INCLUDES    */

// Error handlers
require_once '../class/error.php';

// Telegram err logs integration
require_once '../class/telegram.php';

// SQL get track number for orden name from MS
require_once '../moi_sklad/sql_requests/OrderDetails.php';

require_once 'taobao/TopSdk.php';
require_once 'cainiao.php';
require_once '../vendor/autoload.php';


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


function checkTimeFromPaid($order, $payTime, $credential)
{
    $buyer_login_id = $credential['login'];
    $sessionKey = $credential['sessionKey'];
    $cpCode = $credential['cpCode'];
    $cnId = $credential['cnId'];


    /*  add 3 hour shift from GMT to RU */
    $offsetNow = 3 * 60 * 60;
    $now = strtotime(gmdate("Y-m-d H:i:s")) + $offsetNow;
    /*  add 10 hour shift from PST to RU */
    $offsetAli = 10 * 60 * 60;
    $timeFromPayment = (strtotime($payTime) + $offsetAli) - $now;
    $timeFromPayment = $timeFromPayment / 60 / 60 * (-1);


    /*check if order ready for CAINIAO*/

    $isReady = checkCainiaoReady($order);

    if ($isReady == true) {

        /*run cainiao delivery process*/

        $result = deliverCainiao($order, $cnId, $cpCode, $sessionKey);

        if ($result['result_success'] == true) {
            $message = "Заказ №$order - оформлен в Цайняо и отправлен в Отгрузку";
            telegram($message, '-278688533');

            $orderMS = new OrderMS('', $order, '');
            $orderMSDetails = $orderMS->getByName();
            $orderMS->id = $orderMSDetails['id'];

            /*get order state in MS*/
            preg_match(ID_REGEXP, $orderMSDetails['state']['meta']['href'], $matches);
            $state_id = $matches[0];
            $orderMS->state = $state_id;
            $result['mailNo'] = str_replace(['AEWH', 'RU4'], "", $result['mailNo']);


            $setTrackNumRes = '';
            $setTrackNumRes = $orderMS->setTrackNum($result['mailNo']);
            if (strpos($setTrackNumRes, 'обработка-ошибок') > 0 || $setTrackNumRes == '') {
                telegram("setTrackNum error found " . $result['mailNo'], '-320614744');
                error_log($setTrackNumRes, 3, "setTrackNum.log");
            }

            $setToPackRes = '';
            $setToPackRes = $orderMS->setToPack();
            if (strpos($setToPackRes, 'обработка-ошибок') > 0 || $setToPackRes == '') {
                telegram("setToPack error found $order", '-320614744');
                error_log($setToPackRes, 3, "setToPack.log");
            }


        } else {
            $message = "CAINIAO ОШИБКА $order см ali_order_list_paid.log";
            telegram($message, '-320614744');
            error_log('ali_order_list_paid err' . json_encode($result), 3, 'ali_order_list_paid.log');
        }


    } else {

        /*if not then go next*/
        if ($timeFromPayment >= 2) {

            /*check if order has track in MS*/
            $trackId = false;
//            $trackId = getOrderTrack($order);
            $delivery = getOrderTrack($order);
            $trackId = $delivery['track'];
            $agentId = $delivery['agent'];

            /*has no track*/
            if ($trackId == false) {
//                var_dump($timeFromPayment . PHP_EOL);
                telegram("По заказу Tmall номер $order не заполнен номер трека! Необходимо срочно заполнить.", '-278688533');
            } else {
                /*set track number in Tmall*/

                switch ($agentId) {
                    case "3e974e59-c2ad-11e6-7a69-8f55000291d8":
                        $trackingWebsite = "https://cdek.ru/track.html?order_id=$trackId";
                        break;
                    case "1da99879-55a8-11e7-7a6c-d2a90009f537":
                        $trackingWebsite = "https://iml.ru/status/";
                        break;
                    case "3071006a-d2db-11e9-0a80-025a0021cd0d":
                        $trackingWebsite = '"https://cse.ru/track.php?order=waybill&city_uri=mosrus&lang=rus&number=AEWH0000' . $trackId . 'RU4"';
                        break;

                }

                $c = new TopClient;
                $c->appkey = APPKEY;
                $c->secretKey = SECRET;
                $req = new AliexpressSolutionOrderFulfillRequest;
                $req->setServiceName("OTHER_RU_CITY_RUB");
                $req->setTrackingWebsite("$trackingWebsite");
                $req->setOutRef("$order");
                $req->setSendType("all");
//            $req->setDescription("memo");
                $req->setLogisticsNo("$trackId");
                $resp = $c->execute($req, $sessionKey);
            }
        }

    }


}

function formMasterList($credential)
{


    $buyer_login_id = $credential['login'];
    $sessionKey = $credential['sessionKey'];


    /*additional - 3 hours for RU shift*/
    $offset = 72 * 60 * 60;
    $create_day = date("Y-m-d H:i:s", strtotime(gmdate("Y-m-d H:i:s")) - $offset);

    $c = new TopClient;
    $c->format = "json";
    $c->appkey = APPKEY;
    $c->secretKey = SECRET;
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
                checkTimeFromPaid($shorty['order_id'], $shorty['gmt_pay_time'], $credential);
            }
        }
    }

    return $orderList;
}


foreach (LOGINS as $credential) {
    formMasterList($credential);
}





























