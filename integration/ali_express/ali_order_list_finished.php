<?php

// display results as json
header('Content-Type: application/json');


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set("error_log", "../php-error.log");


define('MS_USERNAME', 'робот_next@техтрэнд');
define('MS_PASSWORD', 'Next0913');
define('MS_PATH', 'https://online.moysklad.ru/api/remap/1.1');
define('MS_LINK', MS_PATH . '/entity/customerorder');
define('ID_REGEXP', '/[0-9a-z]{8}-[0-9a-z]{4}-[0-9a-z]{4}-[0-9a-z]{4}-[0-9a-z]{12}/'); // Регулярка для UUID

define('APPKEY', '27862248');
define('SECRET', 'ca6916e55a087b3561b5077fc8b83ee6');

define('LOGINS', array(
    array(
        'name' => 'Незабудка MR',
        'login' => 'NezabudkaMR@yandex.ru',
        'field_id' => '0bbcd991-81f4-11e9-9109-f8fc0004dec9',
        'sessionKey' => '50002500501Vs151ac533BdqLFskhnQwtwheYk1CiSexTFfFAv6nWUefGArBboUuh8F',
        'cpCode' => 'UTV0a1NLakt5dE9DdzZOdEt1elhnblRnMURQaExvS0w4RVZEVHMyM2o2eTRqUjdiOEdxalpTVjhRN0ZBQldVZA==',
        'cnId' => '4398983084403'
    ),
    array(
        'name' => 'bestgoodsstore',
        'login' => 'bestgoodsstore@yandex.ru',
        'field_id' => '0bbcd3e6-81f4-11e9-9109-f8fc0004dec8',
        'sessionKey' => '50002301b00s17916ea4eTrdXcBvgrz2lkiuhxGTUEjWmoPbPQvsmvYZfkFlqTKVgPr',
        'cpCode' => 'QXJCQk1QcjJKTkZDbHk4ZVZ4bW11cFQ2L2QreW1XT0lJd2ZlMnEvL2dFZC9NbG5CSklEV2tiY0cxNkRSMWlYcQ==',
        'cnId' => '4398985192396'

    ),
    array(
        'name' => 'Новинки',
        'login' => 'novinkiooo@yandex.ru',
        'field_id' => 'e8a40577-77b9-11e9-912f-f3d40003d45d',
        'sessionKey' => '50002500c15tBJCowQScZ6julhS1dea01bfctShXhETAmyVfKkQKwey1lvK3ryD7z1t',
        'cpCode' => 'OTQwTzB2T1U3N1Nza0Y3OVRKMHZyVWtPL0RFRjJHczBqUHBDRHBqK05LVXdBc1pJRkk0THo1YUVLR21PNE5IZQ==',
        'cnId' => '4398985964371'
    ),
    array(
        'name' => 'Незабудка iRobot',
        'login' => 'NezabudkaiRobot@yandex.ru',
        'field_id' => '0bbcde02-81f4-11e9-9109-f8fc0004deca',
        'sessionKey' => '50002500901sP1b7f673cFt7iCP3uQfb1JzgquDxxdddBqgtvwCkmwoWHooj7Cw4dCP',
        'cpCode' => 'czJBM3dFNm9aQ0RuSnhnY0tEK2p2a3g1cEI5aFYwSGw1TlpxTVAyUE1CYk1iYkRCTU1tWENocFo4alU3aFdmUg==',
        'cnId' => '4398985334183'
    ),
    array(
        'name' => 'Незабудка ND',
        'login' => 'NezabudkaND@yandex.ru',
        'field_id' => '0bbce15c-81f4-11e9-9109-f8fc0004decb',
        'sessionKey' => '50002700811duwgr7oCt2sx1edacab6BZ6kWiLtBtQeBGBQfNxYCKlunzemnpZpP21d',
        'cpCode' => 'V1ZDUlZnY09vbHoyQTFpNEZEUElkcGlmUE43Z1hYZEdoVEZwM2huTDlWeWVKUHdIUmY4QmFWV1FOdXVCT3JQeg==',
        'cnId' => '4398983195649'
    )
));


/*  INCLUDES    */

// Error handlers
require_once '../class/error.php';

// Telegram err logs integration
require_once '../class/telegram.php';

// Cancel order in MS
require_once '../moi_sklad/ms_change_order_dynamic.php';

require_once 'taobao/TopSdk.php';


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


function formMasterList($credential)
{

    $buyer_login_id = $credential['login'];
    $sessionKey = $credential['sessionKey'];

    /*catch all for previous 24 hours*/
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
    $param_aeop_order_query->order_status_list = array('FINISH', 'FUND_PROCESSING', 'IN_CANCEL');
    $param_aeop_order_query->page_size = "20";
    $req->setParamAeopOrderQuery(json_encode($param_aeop_order_query));
    $resp = $c->execute($req, $sessionKey);


    $final = $resp->result->target_list->aeop_order_item_dto ?? "";

    $orderList = array();
    if ($final != "") {
        $res = json_encode((array)$final);
        $shortener = json_decode($res, true);
        foreach ($shortener as $shorty) {
            if ($shorty['order_status'] == 'FINISH' || $shorty['order_status'] == 'FUND_PROCESSING') {
                $orderList[$shorty['order_id']] = [
                    'order_status' => $shorty['order_status'],
                    'end_reason' => $shorty['end_reason']
                ];

            } elseif ($shorty['order_status'] == 'IN_CANCEL') {
                $orderList[$shorty['order_id']] = "IN_CANCEL";
            }
        }
    }

    return $orderList;
}


$formed = array();
$final = array();

// add shop value
foreach (LOGINS as $credential) {
    $formed = formMasterList($credential);
    $final[$credential['login']] = $formed;
}
/*send to MS*/

function curlGetOrderMS($link = false, $data = false, $username = false)
{

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($curl, CURLOPT_URL, $link);
    curl_setopt($curl, CURLOPT_HTTPGET, true);

    if (!$username) {
        curl_setopt($curl, CURLOPT_USERPWD, MS_USERNAME . ':' . MS_PASSWORD);
    } else {
        curl_setopt($curl, CURLOPT_USERPWD, $username . ':' . MS_PASSWORD);
    }

    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);
    if ($data) {
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
    }

    $headers = array();
    $headers[] = 'Content-Type: application/json';
    if ($data) {
        $headers[] = 'Content-Length:' . strlen(json_encode($data));
    }

    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

    $result = curl_exec($curl);
    $curl_errno = curl_errno($curl);
    curl_close($curl);

    if ($curl_errno == 0) {
        return $result;
    } else {
        return $curl_errno;
    }

}


function recursive_array_search($needle, $haystack)
{
    foreach ((array)$haystack as $key => $value) {
        if ($needle === $value) {
            return array($key);
        } else if (is_array($value) && $subkey = recursive_array_search($needle, $value)) {
            array_unshift($subkey, $key);
            return $subkey;
        }
    }
}

function getOrderFromMS($reason, $order)
{
    $link = MS_PATH . "/entity/customerorder/?filter=name=" . $order;
    $res = curlGetOrderMS($link, false, MS_USERNAME);

    /*    if search result exists */
    if ($res) {

        $orderSearchResultArrays = json_decode($res, true);
        if ($orderSearchResultArrays['rows']) {
            $row = $orderSearchResultArrays['rows'][0];
            /*get state of order in MS*/
            preg_match(ID_REGEXP, $row['state']['meta']['href'], $matches);
            $state_id = $matches[0];
            /*if not equals Отменен или Возврат*/
            if ($state_id != '327c070c-75c5-11e5-7a40-e8970013993b' && $state_id != '327c05fe-75c5-11e5-7a40-e8970013993a') {
                $attributesArray = $row['attributes'];
                if (isset($row['name'])) {
                    $orderId = $row['name'];
                    $orderLinkMS = $row['meta']['uuidHref'];
                    if ($reason['order_status'] == 'IN_CANCEL' ||
                        ($reason['order_status'] == 'FINISH' && $reason['end_reason'] == 'send_goods_timeout') ||
                        ($reason['order_status'] == 'FINISH' && $reason['end_reason'] == 'buyer_cancel_order')
                    ) {
                        /*pass order id from MS to update to Canceled*/
                        fillOrderTemplate($row['id'], 'cancel');
                    }
                }
                /*look if has tracking number in MS*/
                /*if (recursive_array_search('8a500683-10fc-11ea-0a80-0533000590c8', $res)) {
                    $messageCancelUrgent = "ОТМЕНЕН заказ #[$orderId]($orderLinkMS)";
                    telegram($messageCancelUrgent, '-278688533', 'Markdown');
                }*/
            }
        }


    }
}

//item is cancel reason,key is order id
foreach ($final as $shop => $orders) {
    foreach ($orders as $order => $details) {
        getOrderFromMS($details, $order);
    }

}

//var_dump($final);

























