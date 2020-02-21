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
        'sessionKey' => '50002500131c0AyToBcTKHT7oGv1Muh8zIyiMSiVOiZ10f0e85ceFQ5kUknvbHIgi2z',
        'cpCode' => 'UTV0a1NLakt5dE9DdzZOdEt1elhnblRnMURQaExvS0w4RVZEVHMyM2o2eTRqUjdiOEdxalpTVjhRN0ZBQldVZA==',
        'cnId' => '4398983084403'
    ),
    array(
        'name' => 'bestgoodsstore',
        'login' => 'bestgoodsstore@yandex.ru',
        'field_id' => '0bbcd3e6-81f4-11e9-9109-f8fc0004dec8',
        'sessionKey' => '50002300e00k125e1091EPynqa94I0kqyewPdYEDR8rRXcGePIvgr1mGX4SJq7FiFGn',
        'cpCode' => 'QXJCQk1QcjJKTkZDbHk4ZVZ4bW11cFQ2L2QreW1XT0lJd2ZlMnEvL2dFZC9NbG5CSklEV2tiY0cxNkRSMWlYcQ==',
        'cnId' => '4398985192396'

    ),
    array(
        'name' => 'Новинки',
        'login' => 'novinkiooo@yandex.ru',
        'field_id' => 'e8a40577-77b9-11e9-912f-f3d40003d45d',
        'sessionKey' => '50002500431yXPcbqyGt7pDo4mScackuiLTgtvEZGhw14679d63hls1lHwhbynTgKi0',
        'cpCode' => 'OTQwTzB2T1U3N1Nza0Y3OVRKMHZyVWtPL0RFRjJHczBqUHBDRHBqK05LVXdBc1pJRkk0THo1YUVLR21PNE5IZQ==',
        'cnId' => '4398985964371'
    ),
    array(
        'name' => 'Незабудка iRobot',
        'login' => 'NezabudkaiRobot@yandex.ru',
        'field_id' => '0bbcde02-81f4-11e9-9109-f8fc0004deca',
        'sessionKey' => '50002501b41seTrdXeCxBLxwfJjNp0Hw1AjyjpRjNrVuuP3WimXq515825fcfvhy5Ww',
        'cpCode' => 'czJBM3dFNm9aQ0RuSnhnY0tEK2p2a3g1cEI5aFYwSGw1TlpxTVAyUE1CYk1iYkRCTU1tWENocFo4alU3aFdmUg==',
        'cnId' => '4398985334183'
    ),
    array(
        'name' => 'Незабудка ND',
        'login' => 'NezabudkaND@yandex.ru',
        'field_id' => '0bbce15c-81f4-11e9-9109-f8fc0004decb',
        'sessionKey' => '50002700d259mlqwnwce5l1EixeUuGaDcR5PW19fb431aUDHGPLVIyTGgx4jUXC592o',
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
require_once '../moi_sklad/ms_get_orders_dynamic.php';
//require_once 'ali_order_details_dynamic.php';

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


            $orderMS = new OrderMS('', $order, '');
            $orderMSDetails = $orderMS->getByName();
            $orderMS->id = $orderMSDetails['id'];

            /*get order state in MS*/
            preg_match(ID_REGEXP, $orderMSDetails['state']['meta']['href'], $matches);
            $state_id = $matches[0];
            $orderMS->state = $state_id;
//            $result['mailNo'] = str_replace(['AEWH', 'RU4'], "", $result['mailNo']);


            /*check if stripped mailno 6 chars long*/
            if (strlen($result['mailNo']) >= 6) {
                $setTrackNumRes = $orderMS->setTrackNum($result['mailNo']);
                if (strpos($setTrackNumRes, 'обработка-ошибок') > 0 || $setTrackNumRes == '') {

                    /*try again*/
                    sleep(5);
                    $setTrackNumRes = $orderMS->setTrackNum($result['mailNo']);
                    if (strpos($setTrackNumRes, 'обработка-ошибок') > 0 || $setTrackNumRes == '') {
                        telegram("setTrackNum error found " . $orderMS->name, '-320614744');
                        error_log(date("Y-m-d H:i:s", strtotime(gmdate("Y-m-d H:i:s")) + 3 * 60 * 60) . $setTrackNumRes . " " . $orderMS->name . PHP_EOL, 3, "setTrackNum.log");
                    } else {
                        $setToPackRes = $orderMS->setToPack();
                        if (strpos($setToPackRes, 'обработка-ошибок') > 0 || $setToPackRes == '') {

                            /*try again*/
                            sleep(5);
                            $setToPackRes = $orderMS->setToPack();
                            if (strpos($setToPackRes, 'обработка-ошибок') > 0 || $setToPackRes == '') {
                                telegram("setToPack error found $orderMS->name", '-320614744');
                                error_log(date("Y-m-d H:i:s", strtotime(gmdate("Y-m-d H:i:s")) + 3 * 60 * 60) . $setToPackRes . " " . $orderMS->name . PHP_EOL, 3, "setToPack.log");

                            } else {
                                $message = "Заказ №$order - оформлен в Цайняо и отправлен в Отгрузку";
                                telegram($message, '-278688533');
                                /*set new position price*/
                                setNewPositionPrice($orderMS->name, $credential);
                            }

                        } else {
                            $message = "Заказ №$order - оформлен в Цайняо и отправлен в Отгрузку";
                            telegram($message, '-278688533');
                            /*set new position price*/
                            setNewPositionPrice($orderMS->name, $credential);
                        }
                    }


                } else {

                    $setToPackRes = $orderMS->setToPack();
                    if (strpos($setToPackRes, 'обработка-ошибок') > 0 || $setToPackRes == '') {

                        /*try again*/
                        sleep(5);
                        $setToPackRes = $orderMS->setToPack();
                        if (strpos($setToPackRes, 'обработка-ошибок') > 0 || $setToPackRes == '') {
                            telegram("setToPack error found $orderMS->name", '-320614744');
                            error_log(date("Y-m-d H:i:s", strtotime(gmdate("Y-m-d H:i:s")) + 3 * 60 * 60) . $setToPackRes . " " . $orderMS->name . PHP_EOL, 3, "setToPack.log");
                        } else {
                            $message = "Заказ №$order - оформлен в Цайняо и отправлен в Отгрузку";
                            telegram($message, '-278688533');
                            /*set new position price*/
                            setNewPositionPrice($orderMS->name, $credential);
                        }
                    } else {
                        $message = "Заказ №$order - оформлен в Цайняо и отправлен в Отгрузку";
                        telegram($message, '-278688533');
                        /*set new position price*/
                        setNewPositionPrice($orderMS->name, $credential);
                    }
                }
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

            $delivery = getOrderTrack($order);
            $trackId = $delivery['track'];

            /*has no track*/
            if ($trackId == false) {
                var_dump($timeFromPayment . PHP_EOL);
//                telegram("Трек не заполнен, заказ #$order", '-278688533');
            }
        }

    }


}

function setNewPositionPrice($order, $credential)
{
    $sessionKey = $credential['sessionKey'];

    /*get order details from Aliexp*/

    $findorderbyidRes = findorderbyid($order, $sessionKey);


    /*get order Итого in MS*/

    $link = MS_PATH . "/entity/customerorder/?filter=name=" . $order . "&expand=positions";


    $res = json_decode(curlMS($link), true)['rows'][0];

    $orderMSSum = $res['sum'] / 100;

    $orderMS = new OrderMS($res['id']);


    $pay_amount_by_settlement_cur = (int)$findorderbyidRes['pay_amount_by_settlement_cur'];
    $logistics_amount = $findorderbyidRes['logistics_amount']['cent'] ?? 0;
    $diff = $orderMSSum - $pay_amount_by_settlement_cur + round($logistics_amount / 100);


    $products = $res['positions']['rows'];
    foreach ($products as $product) {
        /*может быть испорчено доставкой*/
        $oldPosTot = $product["quantity"] * $product["price"] / 100;
        $newPosTot = $oldPosTot - $diff * ($oldPosTot / $orderMSSum);
        $newPrice = 100 * $newPosTot / $product["quantity"];

        /*set new prices in MS*/

        $postdata = '{
                  "price": ' . $newPrice . '
                }';


        $setNewPositionPriceResp = $orderMS->updatePositions($postdata, $product['id']);
        /*check if no Errors from MS or check again otherwise send TG message*/
        if (strpos($setNewPositionPriceResp, 'обработка-ошибок') > 0 || $setNewPositionPriceResp == '') {
            telegram("setNewPositionPrice error found " . $order, '-320614744');
            error_log(date("Y-m-d H:i:s", strtotime(gmdate("Y-m-d H:i:s")) + 3 * 60 * 60) . $setNewPositionPriceResp . " " . $order . PHP_EOL, 3, "setNewPositionPriceResp.log");
        }
    }
    /*update comment*/
    $newLines = " Всего скидок для заказа: $diff Сумма была: $orderMSSum, Сумма оплачена $pay_amount_by_settlement_cur, Сумма доставки " . $logistics_amount / 100;
    $oldDescription = $res['description'];
    /*удалить двойные ковычки*/
    $oldDescription = str_replace('"', '', $oldDescription);
    /*удалить новую строку*/
    $oldDescription = preg_replace('/\s+/', ' ', trim($oldDescription));
    $updateComment = '{
                  "description": "' . $oldDescription . $newLines . '"
                }';
    $updateOrderResp = $orderMS->updateOrder($updateComment);
    if (strpos($updateOrderResp, 'обработка-ошибок') > 0 || $updateOrderResp == '') {
        telegram("updateOrderResp error found " . $order, '-320614744');
        error_log(date("Y-m-d H:i:s", strtotime(gmdate("Y-m-d H:i:s")) + 3 * 60 * 60) . $updateOrderResp . " " . $order . PHP_EOL, 3, "updateOrderResp.log");

    }


}


function setTrackToTmall($order, $payTime, $credential)
{

    $sessionKey = $credential['sessionKey'];

    /*check if in MS state is Доставляется and has a track number*/
    $delivery = getOrderTrack($order);

    /*Комплектуется, На выдаче, Доставляется*/
    $statesToShip = array("8beb227b-6088-11e7-7a6c-d2a9003b81a3",
        "8beb25ab-6088-11e7-7a6c-d2a9003b81a4",
        "327c03c6-75c5-11e5-7a40-e89700139938",);


    if (in_array($delivery['state'], $statesToShip) == 1 && $delivery['track'] != false) {
        $trackId = $delivery['track'];
        $agentId = $delivery['agent'];

        /*set track number in Tmall*/

        $serviceName = "OTHER_RU_CITY_RUB";
        switch ($agentId) {
            case "3e974e59-c2ad-11e6-7a69-8f55000291d8":
                $trackingWebsite = "https://cdek.ru/track.html?order_id=$trackId";
                $serviceName = "OTHER_RU_CITY_RUB";
                break;
            case "1da99879-55a8-11e7-7a6c-d2a90009f537":
                $trackingWebsite = "https://iml.ru/status/";
                $serviceName = "OTHER_RU_CITY_RUB";
                break;
            case "3071006a-d2db-11e9-0a80-025a0021cd0d":
//                $trackingWebsite = '"https://cse.ru/track.php?order=waybill&city_uri=mosrus&lang=rus&number=AEWH000' . $trackId . 'RU4"';
                $trackingWebsite = '"https://cse.ru/track.php?order=waybill&city_uri=mosrus&lang=rus&number=' . $trackId . '"';
                $serviceName = "AE_RU_MP_COURIER_PH3_CITY";
//                $trackId = "AEWH000" . $trackId . "RU4";
                break;
        }

        if ($agentId == '3071006a-d2db-11e9-0a80-025a0021cd0d') {
            telegram("Трек отправлен в цаняо для $order $trackId $serviceName", '-320614744');
        }


        $c = new TopClient;
        $c->appkey = APPKEY;
        $c->secretKey = SECRET;
        $req = new AliexpressSolutionOrderFulfillRequest;
        $req->setServiceName($serviceName);
        $req->setTrackingWebsite("$trackingWebsite");
        $req->setOutRef("$order");
        $req->setSendType("all");
//            $req->setDescription("memo");
        $req->setLogisticsNo("$trackId");
        $resp = $c->execute($req, $sessionKey);
        var_dump($resp);

    } else {
        /*  add 3 hour shift from GMT to RU */
        $offsetNow = 3 * 60 * 60;
        $now = strtotime(gmdate("Y-m-d H:i:s")) + $offsetNow;
        /*  add 10 hour shift from PST to RU */
        $offsetAli = 10 * 60 * 60;
        $timeFromPayment = (strtotime($payTime) + $offsetAli) - $now;
        $timeFromPayment = $timeFromPayment / 60 / 60 * (-1);

        if ($timeFromPayment >= 9) {
            telegram("ЗАДЕРЖКА отправки заказа #$order", '-278688533');
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

                /*check if Доставляется in MS and has track num*/
                setTrackToTmall($shorty['order_id'], $shorty['gmt_pay_time'], $credential);


            }
        }
    }

    return $orderList;
}


foreach (LOGINS as $credential) {
    formMasterList($credential);
}






























