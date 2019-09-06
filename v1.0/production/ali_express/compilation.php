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
            error_log('Error compilation '.substr($result, $pos, 120) . PHP_EOL);
        }
    }

    return $result;
}

function formMasterList($credentials)
{

    $login = $credentials['login'];
    $token = $credentials['token'];

//    call cURL
//    $create_day = date("Y-m-d");
    /*UTC - 7 = Pacific Daylight time*/
//    $offset = 7 * 60 * 60;
    /*offset -7 didnt work, catch all for previous 12 hours*/
    $offset = 12 * 60 * 60;
    $create_day = date("Y-m-d H:i:s", strtotime(gmdate("Y-m-d H:i:s")) - $offset);
//    $create_day = "2019-07-28";
    $res = curlAli(array(
        'method' => 'aliexpress.trade.seller.orderlist.get',
        'param_aeop_order_query' => json_encode(array(
            'page_size' => 20,
            'current_page' => 1,
//            'create_date_start' => $create_day . ' 00:00:00',
            'create_date_start' => $create_day,
            'order_status_list' => array('WAIT_SELLER_SEND_GOODS', 'PLACE_ORDER_SUCCESS')
        ))
    ), $login, $token);
    return json_decode($res, true);
}

// run formMaster foreach item in logins array
$final = array();

// add shop value
foreach (LOGINS as $credential) {
    $formed = formMasterList($credential);
    $final[$credential['login']] = $formed;
}
/*count aliexpress.trade.seller.orderlist.get calls foreach LOGIN*/
$getOrderListCount = count(LOGINS);
$offsetGMTRU = 3 * 60 * 60;
$logDate = date("Y-m-d H:i:s", strtotime(gmdate("Y-m-d H:i:s")) + $offsetGMTRU);
$messageQueryCalls = "$getOrderListCount times called from compilation.php due LOGINS at $logDate";
//countQuery($getOrderListCount, $messageQueryCalls);


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






