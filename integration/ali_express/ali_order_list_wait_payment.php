<?php

// display results as json
header('Content-Type: application/json');


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set("error_log", "php-error.log");


define('MS_USERNAME', 'робот_next@техтрэнд');
define('MS_PASSWORD', 'Next0913');
define('MS_PATH', 'https://online.moysklad.ru/api/remap/1.1');
define('MS_LINK', MS_PATH . '/entity/customerorder');
define('ID_REGEXP', '/[0-9a-z]{8}-[0-9a-z]{4}-[0-9a-z]{4}-[0-9a-z]{4}-[0-9a-z]{12}/'); // Регулярка для UUID

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
    )
));


/*  INCLUDES    */

// Error handlers
require_once realpath(dirname(__FILE__) . '/..') . '/class/error.php';

// Telegram err logs integration
require_once realpath(dirname(__FILE__) . '/..') . '/class/telegram.php';

// Cancel order in MS
require_once realpath(dirname(__FILE__) . '/..') . '/moi_sklad/ms_change_order_dynamic.php';

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
    $appkey = $credential['appkey'];
    $secret = $credential['secret'];

    /*catch all for previous 24 hours*/
    $offset = 560 * 60 * 60;
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
    $param_aeop_order_query->order_status_list = array('FINISH', 'FUND_PROCESSING', 'IN_CANCEL');
    $param_aeop_order_query->page_size = "60";
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

    var_dump($res);
    die();
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

























