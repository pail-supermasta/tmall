<?php

// display results as json
//header('Content-Type: application/json');


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set("error_log", "../php-error.log");

/*  INCLUDES    */

// Error handlers
include_once '../class/error.php';

// Telegram err logs integration
require_once '../class/telegram.php';

// Process orders in files
require_once('../file_processing.php');

//create_date_start - PST (pacific time 10 hours moscow difference)
// russian time
//$offset=10*60*60;
//$loaded_from = date("Y-m-d H:i:s",time()+$offset);


require_once 'taobao/TopSdk.php';
define('APPKEY', '27862248');
define('SECRET', 'ca6916e55a087b3561b5077fc8b83ee6');

define('LOGINS', array(
    array(
        'name' => 'Незабудка MR',
        'login' => 'NezabudkaMR@yandex.ru',
        'field_id' => '0bbcd991-81f4-11e9-9109-f8fc0004dec9',
        'sessionKey' => '50002500500V1acb5e0esBdqLFskhnQwtwheYk1CiSexTFfFAv6nWUefGArBboUuh8F'
    ),
    array(
        'name' => 'bestgoodsstore',
        'login' => 'bestgoodsstore@yandex.ru',
        'field_id' => '0bbcd3e6-81f4-11e9-9109-f8fc0004dec8',
        'sessionKey' => '50002301103yrRc163e29d7ZzgUiKwh0xhbCbrgNTyjFHJHwjSsCd5lSPWxJBdCZLQ5'
    ),
    array(
        'name' => 'Новинки',
        'login' => 'novinkiooo@yandex.ru',
        'field_id' => 'e8a40577-77b9-11e9-912f-f3d40003d45d',
        'sessionKey' => '50002500737cfRNerkKCo3qqiYbivGmqfVSjYiCsiryXdglSI1e4adc70E1lvJFkJus'
    ),
    array(
        'name' => 'Незабудка iRobot',
        'login' => 'NezabudkaiRobot@yandex.ru',
        'field_id' => '0bbcde02-81f4-11e9-9109-f8fc0004deca',
        'sessionKey' => '50002500703cfRN14e0f429er9JHvWsscC4j2hLvisve7cDy7nRylIluKtZQNPQCxQu',
    ),
    array(
        'name' => 'Незабудка ND',
        'login' => 'NezabudkaND@yandex.ru',
        'field_id' => '0bbce15c-81f4-11e9-9109-f8fc0004decb',
        'sessionKey' => '50002700532VsBdqLktCoFvXnxeB4JxEjRbWTD7DDviK14d17745W1DmhKcqJnlQq9B'
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


function formMasterList($credential)
{

    $buyer_login_id = $credential['login'];
    $sessionKey = $credential['sessionKey'];


    /*catch all for previous 24 hours*/
    $offset = 24 * 60 * 60;
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
    $param_aeop_order_query->order_status_list = array('WAIT_SELLER_SEND_GOODS', 'PLACE_ORDER_SUCCESS');
    $param_aeop_order_query->page_size = "20";
    $req->setParamAeopOrderQuery(json_encode($param_aeop_order_query));
    $resp = $c->execute($req, $sessionKey);
    $res = json_encode((array)$resp);

    return json_decode($res, true);
}

// run formMaster foreach item in logins array
$final = array();

// add shop value
foreach (LOGINS as $credential) {
    $formed = formMasterList($credential);
    $final[$credential['login']] = $formed;
}



$orderList = json_encode($final);

// search input
$searchStack = json_decode($orderList, true);
// search for key with value in
$lookFor = 'order_id';

function recursiveFind($key, array $searchStack, $lookFor)
{
    // open file ready to write
    $fp = fopen('ali_order_ids/' . $key . '.txt', 'w');
    /*clear all existing content*/

    // write date to file
    $date = new DateTime();
    $date = $date->format("y:m:d h:i:s");
    fwrite($fp, $date . PHP_EOL);

    //recursive part
    $orderIds = array();
    $lastId = 0;
    $iterator = new RecursiveArrayIterator($searchStack);
    $recursive = new RecursiveIteratorIterator(
        $iterator,
        RecursiveIteratorIterator::SELF_FIRST
    );

    /*limit search level by 7, actual level to search is 5*/
    $bottomSearchLevel = 7;

    foreach ($recursive as $key => $value) {
        if ($key === $lookFor && !is_null($value) && $recursive->getDepth() < $bottomSearchLevel) {
            if ($value !== $lastId) {
                array_push($orderIds, $value);
                // check if dublicate
                $lastId = $value;
                // write orderId to file
                fwrite($fp, $value . PHP_EOL);
            }
        }
    }

    //close file
    fclose($fp);

    //end of recursive
    return $orderIds;
}

foreach ($searchStack as $key => $value) {
    try {
        if (is_array($value)) {
            recursiveFind($key, $value, $lookFor);
        } else {
            throw new NotArray();
        }

    } catch (NotArray  $e) {
//        error_log("Caught $e in order $order \n Product list $products");
        telegram("Caught $e when searching for order_id in $searchStack \n", '-320614744');
    }
}

processOrders($files);






