<?php

// display results as json
//header('Content-Type: application/json');
set_time_limit(800);


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set("error_log", "php-error.log");

/*  INCLUDES    */

// Error handlers
include_once realpath(dirname(__FILE__) . '/..') . '/class/error.php';

// Telegram err logs integration
require_once realpath(dirname(__FILE__) . '/..') . '/class/telegram.php';

// Process orders in files
require_once realpath(dirname(__FILE__) . '/..') . '/file_processing.php';

//create_date_start - PST (pacific time 10 hours moscow difference)
// russian time
//$offset=10*60*60;
//$loaded_from = date("Y-m-d H:i:s",time()+$offset);


require_once 'taobao/TopSdk.php';
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
    )
);


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
    $offset = 240 * 60 * 60;
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
    $param_aeop_order_query->order_status_list = array('WAIT_SELLER_SEND_GOODS', 'PLACE_ORDER_SUCCESS');
    $param_aeop_order_query->page_size = "20";
    $req->setParamAeopOrderQuery(json_encode($param_aeop_order_query));
    $resp = $c->execute($req, $sessionKey);
    $res = json_encode((array)$resp);
    var_dump($resp);
    return json_decode($res, true);
//    'RISK_CONTROL', 'PAYMENT_PROCESSING'

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
    $fp = fopen(realpath(dirname(__FILE__)) . '/ali_order_ids/' . $key . '.txt', 'w');
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






