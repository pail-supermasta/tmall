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

require_once 'taobao/TopSdk.php';
require_once realpath(dirname(__FILE__) . '/..') . '/vendor/autoload.php';
require_once realpath(dirname(__FILE__) . '/..') . '/moi_sklad/ms_change_order_dynamic.php';


use Avaks\MS\Orders;

$ordersMS = new Orders();
$ordersWaitPayment = $ordersMS->getWaitPayment();

//get Ждет оплаты из МС

foreach ($ordersWaitPayment as $order) {
    var_dump($order["name"]) ;

    //check if order exists in MS because this Mongo arent up to date
    $link = MS_PATH . "/entity/customerorder/?filter=name=" . $order["name"];
    $res = curlGetOrderMS($link, false, MS_USERNAME);

    $res = json_decode($res, true);
    /*    if search result exists */
    if (sizeof($res['rows']) > 0) {

        switch (true) {
            case stripos($order['description'], "BESTGOODS (ID 5041091)") !== false :
                $shopName = 'BESTGOODS';
                $sessionKey = "50002301029r4LfaZzIzGMtdxRiWieuBmz1cGhQJ41a449a01ITXlFanTOWxJBdCZLQ5";
                $appkey = "32817975";
                $secret = "fc3e140009f59832442d5c195c807fc0";
                break;
            case stripos($order['description'], "orion (ID 911725024)") !== false :
                $shopName = 'orion';
                $sessionKey = "50002201211qy8OzguEiR9T194d19ebvE7Girftw0dmHtGxmyX9d28OxEySXGK37wpOd";
                $appkey = "32817975";
                $secret = "fc3e140009f59832442d5c195c807fc0";

                break;

        }
        $tmallOrder = findorderbyid($order['name'], $sessionKey, $appkey, $secret);

        var_dump($tmallOrder['order_end_reason']);
        $oldDescription = $order['description'];
        /*удалить двойные ковычки*/
        $oldDescription = str_replace('"', '', $oldDescription);
        /*удалить новую строку*/
        $oldDescription = preg_replace('/\s+/', ' ', trim($oldDescription));
        $order_end_reason = $tmallOrder['order_end_reason'] ? '\n Причина завершения заказа: ' . $tmallOrder['order_end_reason'] : '';
        $update = false;

        if ($tmallOrder['order_status'] == 'IN_CANCEL' ||
            ($tmallOrder['order_status'] == 'FINISH' && $tmallOrder['order_end_reason'] == 'send_goods_timeout') ||
            ($tmallOrder['order_status'] == 'FINISH' && $tmallOrder['order_end_reason'] == 'buyer_cancel_order')
        ) {
            $state = '327c070c-75c5-11e5-7a40-e8970013993b'; //Отменен
            $update = true;
        } else if ($tmallOrder['order_status'] == 'FINISH') {
            $state = '327c04c9-75c5-11e5-7a40-e89700139939'; //Завершен
            $update = true;
        }

        if ($update) {
            $postdata = '{
            "state": {
                "meta": {
                    "href": "https://online.moysklad.ru/api/remap/1.1/entity/customerorder/metadata/states/$state",
                    "type": "state",
                    "mediaType": "application/json"
                }
            },
            "description": "' . $oldDescription . $order_end_reason . '"}';
            var_dump($postdata);


        $res = curlMSFinish('робот_next@техтрэнд', $postdata, $order['id']);
        var_dump($res);
        die();
        }

    }
}


function getToken($url, $data)
{
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    $res = curl_exec($ch);
    $result = json_decode($res, true);
    curl_close($ch);
    return $result['token'];
}

function getData($urlProduct, $data, $token)
{
    $headers = array(
        'Content-Type: application/x-www-form-urlencoded',
        sprintf('Authorization: Bearer %s', $token)
    );

    $data_string = http_build_query($data);

    $ch = curl_init($urlProduct);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
    curl_setopt($ch, CURLOPT_URL, $urlProduct . '/?' . $data_string);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $res = curl_exec($ch);
    $result = json_decode($res, true);
    curl_close($ch);

    return $result;
}

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


/*ALIEX*/


function findorderbyid($post_data, $sessionKey, $appkey, $secret)
{

    $c = new TopClient;
    $c->format = "json";
    $c->appkey = $appkey;
    $c->secretKey = $secret;
    $req = new AliexpressSolutionOrderInfoGetRequest;
    $param1 = new OrderDetailQuery;
    $param1->order_id = $post_data;
    $req->setParam1(json_encode($param1));
    $resp = $c->execute($req, $sessionKey);

    if (!isset($resp->result)) {
        return false;
    } else {
        $result = $resp->result->data;
        $res = (array)$result;
    }

    return $res;
}



























