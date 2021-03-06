<?php

// display results as json
header('Content-Type: application/json');


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set("error_log", "php-error.log");

define('MS_HOST', 'avaks.org');
define('MS_USER', 'avaks');
define('MS_PASS', 'SbTZN8L9fCpVDxtc');
define('MS_DB', 'avaks');


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
    ),
    array(
        'appkey' => '32817975', //aliexpr app key orion
        'secret' => 'fc3e140009f59832442d5c195c807fc0', //aliexpr app secret orion
        'name' => 'novinkiooo',
        'login' => 'novinkiooo@yandex.ru',
        'field_id' => 'e8a40577-77b9-11e9-912f-f3d40003d45d',
        'sessionKey' => '50002500721djOAer7LBO3mQAZ5jVdhrE1730b9ebRsc7FeT4LrwHiIRmawEnCmZwzue',
        'cpCode' => 'OTQwTzB2T1U3N1Nza0Y3OVRKMHZyVWtPL0RFRjJHczBqUHBDRHBqK05LVXdBc1pJRkk0THo1YUVLR21PNE5IZQ==',
        'cnId' => '4398985964371',
    )
));


/*  INCLUDES    */

// Error handlers
require_once realpath(dirname(__FILE__) . '/..') . '/class/error.php';

// Telegram err logs integration
require_once realpath(dirname(__FILE__) . '/..') . '/class/telegram.php';

// Cancel order in MS
require_once realpath(dirname(__FILE__) . '/..') . '/moi_sklad/ms_change_order_dynamic.php';
require_once realpath(dirname(__FILE__) . '/..') . '/moi_sklad/ms_get_orders_dynamic.php';
require_once 'ali_order_details_dynamic.php';

require_once 'taobao/TopSdk.php';
require_once realpath(dirname(__FILE__) . '/..') . '/vendor/autoload.php';


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


//WAIT_SELLER_SEND_GOODS


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
    $param_aeop_order_query->order_status_list = array('WAIT_SELLER_SEND_GOODS');
    $param_aeop_order_query->page_size = "20";
    $req->setParamAeopOrderQuery(json_encode($param_aeop_order_query));
    $resp = $c->execute($req, $sessionKey);
    $res = json_encode((array)$resp);

    return json_decode($res, true);
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
    foreach ($haystack as $key => $value) {
        if ($needle === $value) {
            return array($key);
        } else if (is_array($value) && $subkey = recursive_array_search($needle, $value)) {
            array_unshift($subkey, $key);
            return $subkey;
        }
    }
}

function setNewPositionPrice($order, $credential)
{
    $sessionKey = $credential['sessionKey'];
    $appkey = $credential['appkey'];
    $secret = $credential['secret'];

    /*get order details from Aliexp*/

    $findorderbyidRes = findorderbyid($order, $sessionKey, $appkey, $secret);


    /*get order Итого in MS*/

    $link = MS_PATH . "/entity/customerorder/?filter=name=" . $order . "&expand=positions";


    $res = json_decode(curlMS($link), true)['rows'][0];

    $orderMSSum = $res['sum'] / 100;

    $orderMS = new OrderMS($res['id']);


    $pay_amount_by_settlement_cur = $findorderbyidRes['pay_amount_by_settlement_cur'];
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

    $productsAli = $findorderbyidRes['child_order_list']['global_aeop_tp_child_order_dto'];
    $affiliateFeeSum = 0;
    $affiliateAmount = 0;

    foreach ($productsAli as $productAli) {
        if (isset($productAli['afflicate_fee_rate'])) {
            $affiliateFeeSum += $productAli['afflicate_fee_rate'];
        }
    }

    /*Affiliate fee*/
    $affiliateFee = $affiliateFeeSum / sizeof($productsAli);
    $affiliateAmount = ($pay_amount_by_settlement_cur - $logistics_amount / 100) * $affiliateFee;

    foreach ($res['attributes'] as $attribute){
        if ($attribute['id']=='535dd809-1db1-11ea-0a80-04c00009d6bf'){
            $dshSum = $attribute['value'] + $affiliateAmount;
            break;
        }
    }

    /*update comment*/
    $newLines = " Affiliate fee: $affiliateAmount. Всего скидок для заказа: $diff Сумма была: $orderMSSum, Сумма оплачена $pay_amount_by_settlement_cur, Сумма доставки " . $logistics_amount / 100;
    $oldDescription = $res['description'];
    /*удалить двойные ковычки*/
    $oldDescription = str_replace('"', '', $oldDescription);
    /*удалить новую строку*/
    $oldDescription = preg_replace('/\s+/', ' ', trim($oldDescription));
    $postdata2 = '{
                  "description": "' . $oldDescription . $newLines . '",
                  "attributes": [{
                        "id": "535dd809-1db1-11ea-0a80-04c00009d6bf",
                        "value": ' . $dshSum . '
                  }]
                }';
    $updateOrderResp = $orderMS->updateOrder($postdata2);
    if (strpos($updateOrderResp, 'обработка-ошибок') > 0 || $updateOrderResp == '') {
        telegram("updateOrderResp error found " . $order, '-320614744');
        error_log(date("Y-m-d H:i:s", strtotime(gmdate("Y-m-d H:i:s")) + 3 * 60 * 60) . $updateOrderResp . " " . $order . PHP_EOL, 3, "updateOrderResp.log");

    }


}

function getOrderFromMS($order, $credential)
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

                /*if equals Ждем оплаты */
                if ($state_id == '327c0111-75c5-11e5-7a40-e89700139936') {
                    if (isset($row['name'])) {
                        $orderId = $row['name'];
                        $oldDescription = $row['description'];

                        /*pass order id from MS to update to Paid*/
                        fillOrderTemplate($row['id'], 'paid', $oldDescription);

                        /*set new position price*/
                        setNewPositionPrice($orderId, $credential);

                    }
                }
            }
        }


    }
}


$formed = array();
$final = array();
// add shop value
foreach (LOGINS as $credential) {
    $formed = formMasterList($credential);
    if (isset($formed['result']['target_list']['aeop_order_item_dto'])) {
        foreach ($formed['result']['target_list']['aeop_order_item_dto'] as $shorty) {
            getOrderFromMS($shorty['order_id'], $credential);
        }
    }

}
















