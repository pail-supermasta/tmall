<?php

// display results as json
header('Content-Type: application/json');


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set("error_log", "../php-error.log");


/*  INCLUDES    */

// Error handlers
//require_once '../class/error.php';

// Telegram err logs integration
require_once '../class/telegram.php';


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
    return $result;
}

function formMasterList($credentials)
{

    $login = $credentials['login'];
    $token = $credentials['token'];

//    call cURL
    /*additional - 3 hours for RU shift*/
    $offsetGet = 45 * 60 * 60;
    $getForTwoDays = date("Y-m-d H:i:s", strtotime(gmdate("Y-m-d H:i:s")) - $offsetGet);
//        echo $getForTwoDays . PHP_EOL;

//    $create_day = "2019-07-10 00:00:00";
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
    $shortener = isset($res['aliexpress_trade_seller_orderlist_get_response']) ? $res['aliexpress_trade_seller_orderlist_get_response']['result']['target_list']['aeop_order_item_dto'] : 0;
    if (is_array($shortener)) {
        foreach ($shortener as $shorty) {
            if ($shorty['order_status'] == 'WAIT_SELLER_SEND_GOODS') {
                $orderList[$shorty['order_id']] = $shorty['gmt_pay_time'];
                checkTimeFromPaid($shorty['order_id'],$shorty['gmt_pay_time']);
            }
        }
    }

    return $orderList;
}


/*$formed = array();
$final = array();*/
// add shop value
foreach (LOGINS as $credential) {
//    array_push($formed, formMasterList($credential));
    formMasterList($credential);

}
/*var_dump($formed);*/

//вести выборку оплаченных заказов за 2 дня
//сохранять как номер заказа + время попадания в список


/*$str = 'Bormer|2029-04-01 16:40//Herard|2015-04-25 14:40//FEF|2030-04-01 16:40//Jaskd|2015-04-25 14:40';

$masa = explode("//", $str);
$final = array();
foreach ($masa as $mas) {
    list($k, $v) = explode('|', $mas);
    $result[$k] = $v;
}

var_dump($result);*/

//сравнить текущее время и дату
function checkTimeFromPaid($order,$payTime)
{


    $offsetNow = 3 * 60 * 60;
    $now = strtotime(gmdate("Y-m-d H:i:s")) + $offsetNow;
    /*    $now = date("Y-m-d H:i:s", strtotime(gmdate("Y-m-d H:i:s")) + $offsetNow);
        echo $now . PHP_EOL;*/

// $shortener['gmt_pay_time']='2019-08-01 19:06:59';

    /*  add 10 hour shift from PST to RU */
    $offsetAli = 10 * 60 * 60;
    /*    $pay_date = date("Y-m-d H:i:s", strtotime($payTime) + $offsetAli);
        echo $pay_date . PHP_EOL;*/

    $timeFromPayment = (strtotime($payTime) + $offsetAli) - $now;
    $timeFromPayment = $timeFromPayment / 60 / 60 * (-1);

    if ($timeFromPayment >= 2) {
        telegram("По заказу Tmall номер $order не заполнен номер трека! Необходимо срочно заполнить.", '-278688533');
//        echo "$timeFromPayment H ";
    }

//далее по алгоритму
}

//checkTimeFromPaid('2019-08-01 19:06:59');
























