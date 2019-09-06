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

/*  INCLUDES    */

// Error handlers
//require_once '../class/error.php';

// Telegram err logs integration
require_once '../class/telegram.php';

// SQL get track number for orden name from MS
require_once '../moi_sklad/sql_requests/get_order_track.php';
// Set track number in Tmall
require_once 'ali_set_track_num_dynamic.php';


define('LOGINS', array(
    array(
        'name' => 'Новинки',
        'login' => 'novinkiooo@yandex.ru',
        'field_id' => 'e8a40577-77b9-11e9-912f-f3d40003d45d',
        'token' => 'AVAKS1dji424aa97c-3fc38-4b18-a0ed-12642019G',
    ),
    array(
        'name' => 'bestgoodsstore',
        'login' => 'bestgoodsstore@yandex.ru',
        'field_id' => '0bbcd3e6-81f4-11e9-9109-f8fc0004dec8',
        'token' => '2eb8s5gt3b8554viida367-34be7205fd9d',
    ),
    array(
        'name' => 'Незабудка ND',
        'login' => 'NezabudkaND@yandex.ru',
        'field_id' => '0bbce15c-81f4-11e9-9109-f8fc0004decb',
        'token' => 'pnrlgy68ein88wqiz5qsibb0vxnh5d5d81o2lpv2',
    ),
    array(
        'name' => 'Незабудка MR',
        'login' => 'NezabudkaMR@yandex.ru',
        'field_id' => '0bbcd991-81f4-11e9-9109-f8fc0004dec9',
        'token' => 'i3j0c3sjtrzclztjp6dsishmt55264dw51rrvep3',
    ),
    array(
        'name' => 'Незабудка iRobot',
        'login' => 'NezabudkaiRobot@yandex.ru',
        'field_id' => '0bbcde02-81f4-11e9-9109-f8fc0004deca',
        'token' => 'jizsgu7g09sw02tkpdj85by0wgthofgpkv7re2k7',
    )
));

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

/*Count queries made to Aliexpress api over the day*/
function countQuery($times, $messageQueryCalls)
{
    /*get current quantity from file*/
    $queryCountFilename = 'query-count.txt';
    $fp = fopen($queryCountFilename, "r");
    if (flock($fp, LOCK_SH)) {
        $fileOut = fread($fp, filesize($queryCountFilename));
        // release lock
        flock($fp, LOCK_UN);
    }
    fclose($fp);
    $fileOutInt = (int)$fileOut;


    /*clear all existing content and update value*/
    $queryCount = $fileOutInt + $times;
    $fp = fopen($queryCountFilename, "w");
    // exclusive lock
    if (flock($fp, LOCK_EX)) {
        fwrite($fp, $queryCount);
        // release lock
        flock($fp, LOCK_UN);
    }
    fclose($fp);

    error_log($messageQueryCalls . " \n", 3, "aliapi-calls-count.log");
}


// get order list for store
function curlAli($post_data, $ALI_LOGIN, $ALI_TOKEN)
{

    $ALI_KEY = md5($ALI_LOGIN . gmdate('dmYH') . $ALI_TOKEN);
    $ALI_URL = 'https://alix.brand.company/api_top3/';
    $headers = array(
        "Content-Type: multipart/form-data",
        "Accept: application/json;charset=UTF-8",
        "Authorization: AccessToken {$ALI_LOGIN}:{$ALI_KEY}",
    );

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $ALI_URL);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($curl);

    if (curl_errno($curl)) {
        error_log(curl_errno($curl));
    }
    $search = 'errorCode":"1';
    $found = strpos_recursive($result, $search);
    if ($found) {
        foreach ($found as $pos) {
            error_log(substr($result, $pos, 120) . PHP_EOL);
        }
    }
    return $result;
}

function checkTimeFromPaid($order, $payTime, $login, $token)
{
    /*  add 3 hour shift from GMT to RU */
    $offsetNow = 3 * 60 * 60;
    $now = strtotime(gmdate("Y-m-d H:i:s")) + $offsetNow;
    /*  add 10 hour shift from PST to RU */
    $offsetAli = 10 * 60 * 60;
    $timeFromPayment = (strtotime($payTime) + $offsetAli) - $now;
    $timeFromPayment = $timeFromPayment / 60 / 60 * (-1);


    if ($timeFromPayment >= 2) {

        /*check if order has track in MS*/
        $trackId = false;
        $trackId = getOrderTrack($order);
        /*has no track*/
        if ($trackId == false) {
            var_dump($timeFromPayment . PHP_EOL);
            telegram("По заказу Tmall номер $order не заполнен номер трека! Необходимо срочно заполнить.", '-278688533');
        } else {
            /*set track number in Tmall*/
            $res = curlPostTrack(array(
                "method" => "aliexpress.solution.order.fulfill",
                "service_name" => "OTHER_RU_CITY_RUB",
                "tracking_website" => "https://www.pochta.ru/",
                "out_ref" => "$order",
                "send_type" => "all",
                "logistics_no" => "$trackId"
            ), $login, $token);
            echo $res;
            echo $trackId . PHP_EOL;
        }
    }
    var_dump($order . $timeFromPayment . PHP_EOL);

}

function formMasterList($credentials)
{

    $login = $credentials['login'];
    $token = $credentials['token'];


    /*additional - 3 hours for RU shift*/
    $offsetGet = 45 * 60 * 60;
    $getForTwoDays = date("Y-m-d H:i:s", strtotime(gmdate("Y-m-d H:i:s")) - $offsetGet);

    $res = curlAli(array(
        'method' => 'aliexpress.trade.seller.orderlist.get',
        'param_aeop_order_query' => json_encode(array(
            'page_size' => 20,
            'current_page' => 1,
            'create_date_start' => $getForTwoDays,
            'order_status_list' => 'WAIT_SELLER_SEND_GOODS'
        ))
    ), $login, $token);
    $orderList = array();
    $res = json_decode($res, true);
    $shortener = isset($res['aliexpress_trade_seller_orderlist_get_response']['result']['target_list']['aeop_order_item_dto']) ? $res['aliexpress_trade_seller_orderlist_get_response']['result']['target_list']['aeop_order_item_dto'] : 0;
    if (is_array($shortener)) {
        foreach ($shortener as $shorty) {
            if ($shorty['order_status'] == 'WAIT_SELLER_SEND_GOODS') {
                $orderList[$shorty['order_id']] = $shorty['gmt_pay_time'];

                /*check if paid and no track number*/
                checkTimeFromPaid($shorty['order_id'], $shorty['gmt_pay_time'], $login, $token);
            }
        }
    }

    return $orderList;
}


foreach (LOGINS as $credential) {
    formMasterList($credential);
}

/*count aliexpress.trade.seller.orderlist.get calls foreach LOGIN*/
$getOrderListCount = count(LOGINS);
$offsetGMTRU = 3 * 60 * 60;
$logDate = date("Y-m-d H:i:s", strtotime(gmdate("Y-m-d H:i:s")) + $offsetGMTRU);
$messageQueryCalls = "$getOrderListCount times called from ali_order_list_paid.php due LOGINS at $logDate";
//countQuery($getOrderListCount, $messageQueryCalls);





























