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
        'sessionKey' => '50002500500V1acb5e0esBdqLFskhnQwtwheYk1CiSexTFfFAv6nWUefGArBboUuh8F',
        'cpCode' => 'UTV0a1NLakt5dE9DdzZOdEt1elhnblRnMURQaExvS0w4RVZEVHMyM2o2eTRqUjdiOEdxalpTVjhRN0ZBQldVZA==',
        'cnId' => '4398983084403'
    ),
    array(
        'name' => 'bestgoodsstore',
        'login' => 'bestgoodsstore@yandex.ru',
        'field_id' => '0bbcd3e6-81f4-11e9-9109-f8fc0004dec8',
        'sessionKey' => '50002301103yrRc163e29d7ZzgUiKwh0xhbCbrgNTyjFHJHwjSsCd5lSPWxJBdCZLQ5',
        'cpCode' => 'QXJCQk1QcjJKTkZDbHk4ZVZ4bW11cFQ2L2QreW1XT0lJd2ZlMnEvL2dFZC9NbG5CSklEV2tiY0cxNkRSMWlYcQ==',
        'cnId' => '4398985192396'

    ),
    array(
        'name' => 'Новинки',
        'login' => 'novinkiooo@yandex.ru',
        'field_id' => 'e8a40577-77b9-11e9-912f-f3d40003d45d',
        'sessionKey' => '50002500737cfRNerkKCo3qqiYbivGmqfVSjYiCsiryXdglSI1e4adc70E1lvJFkJus',
        'cpCode' => 'OTQwTzB2T1U3N1Nza0Y3OVRKMHZyVWtPL0RFRjJHczBqUHBDRHBqK05LVXdBc1pJRkk0THo1YUVLR21PNE5IZQ==',
        'cnId' => '4398985964371'
    ),
    array(
        'name' => 'Незабудка iRobot',
        'login' => 'NezabudkaiRobot@yandex.ru',
        'field_id' => '0bbcde02-81f4-11e9-9109-f8fc0004deca',
        'sessionKey' => '50002500703cfRN14e0f429er9JHvWsscC4j2hLvisve7cDy7nRylIluKtZQNPQCxQu',
        'cpCode' => 'czJBM3dFNm9aQ0RuSnhnY0tEK2p2a3g1cEI5aFYwSGw1TlpxTVAyUE1CYk1iYkRCTU1tWENocFo4alU3aFdmUg==',
        'cnId' => '4398985334183'
    ),
    array(
        'name' => 'Незабудка ND',
        'login' => 'NezabudkaND@yandex.ru',
        'field_id' => '0bbce15c-81f4-11e9-9109-f8fc0004decb',
        'sessionKey' => '50002700532VsBdqLktCoFvXnxeB4JxEjRbWTD7DDviK14d17745W1DmhKcqJnlQq9B',
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

        $result = deliverCainiao($order,$cnId,$cpCode,$sessionKey);

        if ($result['result_success'] == true) {
            $message = "Заказ №$order - оформлен в Цайняо и отправлен в Отгрузку";
            telegram($message, '-278688533');

            $orderMS = new OrderMS('',$order,'');
            $orderMSDetails = $orderMS->getByName();
            $orderMS->id = $orderMSDetails['id'];

            /*get order state in MS*/
            preg_match(ID_REGEXP, $orderMSDetails['state']['meta']['href'], $matches);
            $state_id = $matches[0];
            $orderMS->state = $state_id;
            $result['mailNo'] = str_replace(['AEWH', 'RU4'],"",$result['mailNo']);

            $orderMS->setTrackNum($result['mailNo']);
            $message = "Для заказа №$order будет установлен трек ".$result['mailNo']." в МС";
            telegram($message, '-278688533');
            /*if В работе  - set Отгрузить */
            if ($orderMS->state == 'ecf45f89-f518-11e6-7a69-9711000ff0c4') {
                $message = "Статус заказа №$order будет установлен с В работе на ОТГРУЗИТЬ в МС";
                telegram($message, '-278688533');
                $orderMS->setToPack();
            } else{
                $message = "ВНИМАНИЕ! Заказ №$order - не может быть отправлен Отгрузку. Статус не В работе.";
                telegram($message, '-278688533');
            }

        } else {
            $message = "CAINIAO ОШИБКА $order";
            telegram($message, '-320614744');
        }


    } else {

        /*if not then go next*/
        if ($timeFromPayment >= 2) {

            /*check if order has track in MS*/
            $trackId = false;
            $trackId = getOrderTrack($order);

            /*has no track*/
            if ($trackId == false) {
//                var_dump($timeFromPayment . PHP_EOL);
                telegram("По заказу Tmall номер $order не заполнен номер трека! Необходимо срочно заполнить.", '-278688533');
            } else {
                /*set track number in Tmall*/

                $c = new TopClient;
                $c->appkey = APPKEY;
                $c->secretKey = SECRET;
                $req = new AliexpressSolutionOrderFulfillRequest;
                $req->setServiceName("OTHER_RU_CITY_RUB");
                $req->setTrackingWebsite("https://cse.ru/track.php");
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





























