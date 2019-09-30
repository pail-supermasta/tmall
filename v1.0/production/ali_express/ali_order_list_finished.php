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


/*  INCLUDES    */

// Error handlers
require_once '../class/error.php';

// Telegram err logs integration
require_once '../class/telegram.php';

// Cancel order in MS
require_once '../moi_sklad/ms_change_order_dynamic.php';


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
            error_log('Error ali_order_list_finished '.substr($result, $pos, 120) . PHP_EOL);
        }
    }
    return $result;

}

function formMasterList($credentials)
{

    $login = $credentials['login'];
    $token = $credentials['token'];

//    call cURL
    $offset = 24 * 60 * 60;
    $create_day = date("Y-m-d H:i:s", strtotime(gmdate("Y-m-d H:i:s")) - $offset);
//    $create_day = "2019-07-20 00:00:00";
    $res = curlAli(array(
        'method' => 'aliexpress.trade.seller.orderlist.get',
        'param_aeop_order_query' => json_encode(array(
            'page_size' => 20,
            'current_page' => 1,
//            'create_date_start' => $create_day . ' 00:00:00',
            'create_date_start' => $create_day,
            'order_status_list' => array('FINISH', 'FUND_PROCESSING', 'IN_CANCEL')
        ))
    ), $login, $token);

    $orderList = array();
    $res = json_decode($res, true);
    $shortener = isset($res['aliexpress_trade_seller_orderlist_get_response']['result']['target_list']['aeop_order_item_dto']) ? $res['aliexpress_trade_seller_orderlist_get_response']['result']['target_list']['aeop_order_item_dto'] : 0;
    if (is_array($shortener)) {
        foreach ($shortener as $shorty) {
            if ($shorty['order_status'] == 'FINISH' || $shorty['order_status'] == 'FUND_PROCESSING') {
                if ($shorty['end_reason'] == 'buyer_cancel_order_in_risk' || $shorty['end_reason'] == 'buyer_cancel_notpay_order' || $shorty['end_reason'] == 'pay_timeout' || $shorty['end_reason'] == 'buyer_cancel_order') {
//                echo 'order ' . $shorty['order_id'] . ' canceled due ' . $shorty['end_reason'] . PHP_EOL;
                    $orderList[$shorty['order_id']] = $shorty['end_reason'];
                }
            } elseif ($shorty['order_status'] == 'IN_CANCEL'){
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
    array_push($final, $formed);
}

/*count aliexpress.trade.seller.orderlist.get calls foreach LOGIN*/
$getOrderListCount = count(LOGINS);
$offsetGMTRU = 3 * 60 * 60;
$logDate = date("Y-m-d H:i:s", strtotime(gmdate("Y-m-d H:i:s")) + $offsetGMTRU);
$messageQueryCalls = "$getOrderListCount times called from ali_order_list_finished.php due LOGINS at $logDate";
//countQuery($getOrderListCount, $messageQueryCalls);

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
    foreach ($haystack as $key => $value) {
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
            foreach ($orderSearchResultArrays['rows'] as $row) {
                /*get state of order in MS*/
                preg_match(ID_REGEXP, $row['state']['meta']['href'], $matches);
                $state_id = $matches[0];
                /*if not equals Отменен или Возврат*/
                if ($state_id != '327c070c-75c5-11e5-7a40-e8970013993b' && $state_id != '327c05fe-75c5-11e5-7a40-e8970013993a') {
                    $attributesArray = $row['attributes'];
                    if (isset($row['name'])) {
                        $orderId = $row['name'];
                        $orderLinkMS = $row['meta']['uuidHref'];
                    }
                    /*look if has tracking number in MS*/
                    if (recursive_array_search('a446677c-46b8-11e7-7a34-5acf0031d7b9', $res)) {
                        $messageCancelUrgent = "ВНИМАНИЕ. Заказ с ТРЕК НОМЕРОМ [$orderId]($orderLinkMS) попал в статус ОТМЕНЕН. СРОЧНО обработать.";
                        telegram($messageCancelUrgent, '-278688533', 'Markdown');
                    }

                    /*echo $order . ' with ' . $state_id . ' is not Canceled in MS; ';
                    echo $order . ' canceled in Tmall due: ' . $reason . PHP_EOL;*/

                    /*pass order id from MS to update to Canceled*/
                    fillOrderTemplate($row['id'], 'cancel');
                }
            }
        }


    }
}

//item is cancel reason,key is order id
array_walk_recursive($final, function ($item, $key) {
//    echo "$key holds $item\n";
    getOrderFromMS($item, $key);
});












