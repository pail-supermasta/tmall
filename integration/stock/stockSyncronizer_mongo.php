<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 09.09.2019
 * Time: 16:32
 */

error_reporting(E_ALL);
ini_set("display_errors", 1);
ini_set("error_log", "php-error.log");
set_time_limit(1200);

// Список складов для расчёта остатков

define('APPKEY', '30833672');
define('SECRET', '1021396785b2eaa1497b7a58dddf19b3');

$login = array(
    'name' => 'bestgoodsstore',
    'login' => 'bestgoodsstore@yandex.ru',
    'field_id' => '0bbcd3e6-81f4-11e9-9109-f8fc0004dec8',
    'sessionKey' => '50002300413yAdDbqygrAkmv21cf1a94bsqga2hwEpqARrGXkfThpxxhkZxBBRHfZ7x',
    'cpCode' => 'QXJCQk1QcjJKTkZDbHk4ZVZ4bW11cFQ2L2QreW1XT0lJd2ZlMnEvL2dFZC9NbG5CSklEV2tiY0cxNkRSMWlYcQ==',
    'cnId' => '4398985192396'

);




// Telegram err logs integration
require_once realpath(dirname(__FILE__) . '/..') . '/class/telegram.php';

require_once realpath(dirname(__FILE__) . '/..') . '/ali_express/taobao/TopSdk.php';

require_once realpath(dirname(__FILE__) . '/..') . '/vendor/autoload.php';


use Avaks\MS\Products;
use Avaks\AE\Product;
use Avaks\MS\Bundles;

$productsMS = new Products();
$bundlesMS = new Bundles();
$syncErrors = '';

$urlLogin = 'https://api.backendserver.ru/api/v1/auth/login';
$userData = array("username" => "mongodb@техтрэнд", "password" => "!!@th9247t924");

$urlProduct = 'https://api.backendserver.ru/api/v1/product';
$urlBundle = 'https://api.backendserver.ru/api/v1/bundle';
$urlStock = 'https://api.backendserver.ru/api/v1/report_stock_all';


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

function getQuantity($urlProduct, $token)
{

    $data['limit'] = 999999;
    $data['offset'] = 0;
    $data['project'] = json_encode(array(
            '_id' => true,
            '_product' => true,
            'quantity' => true,
            'reserve' => true,
            'stock' => true,
            'updated' => true
        )
    );

    $data['filter'] = json_encode(array('_store' => '48de3b8e-8b84-11e9-9ff4-34e8001a4ea1'));

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

$start = microtime(TRUE);

//$token = getToken($urlLogin, $userData);
$token = "bW9uZ29kYkDRgtC10YXRgtGA0Y3QvdC0OiEhQHRoOTI0N3Q5MjQ=";

/**************STOCK*****************/
$stocks = getQuantity($urlStock, $token);
$stockMS = [];

if ($stocks['rows']) {
    foreach ($stocks['rows'] as $k => $stock) {

        if (!isset($stock['reserve'])) {
            $available = $stock['stock'];
        } else {
            $available = $stock['stock'] - $stock['reserve'];
        }

        if ($available <= 0) {
            $available = 0;
        }
        $stockMS[$stock['_product']] = array('available' => $available, 'updated' => $stock['updated']);
    }
}

/**************PRODUCT*****************/

$data['filter'] = json_encode(array('_attributes.TMall ID (Best Goods)' => ['$exists'=>true]));

$data['limit'] = 9999;
$data['offset'] = 0;
$data['project'] = json_encode(array(
        '_id' => true,
        '_attributes.TMall ID (Best Goods)' => true,
        'code' => true,
    )
);

$products = getData($urlProduct, $data, $token);

foreach ($products['rows'] as $key => $product) {

    // Получаем ID товара в Aliexpress
    if (!isset($product['_attributes']['TMall ID (Best Goods)']) || $product['_attributes']['TMall ID (Best Goods)'] == '') continue;

    $aliProduct = new Product($product['_attributes']['TMall ID (Best Goods)'], $product['code']);

    $response = $aliProduct->setStock($stockMS[$product['_id']]['available'], $login);
    var_export($response);
}
$end = microtime(TRUE);

/**************BUNDLE*****************/


$data['filter'] = json_encode(array('_attributes.TMall ID (Best Goods)' => ['$exists'=>true]));

$data['limit'] = 9999;
$data['offset'] = 0;
$data['project'] = json_encode(array(
        '_id' => true,
        '_attributes.TMall ID (Best Goods)' => true,
        'code' => true,
    )
);

$bundles = getData($urlBundle, $data, $token);

foreach ($bundles['rows'] as $key => $bundle) {

    // Получаем ID товара в Aliexpress
    if (!isset($bundle['_attributes']['TMall ID (Best Goods)']) || $bundle['_attributes']['TMall ID (Best Goods)'] == '') continue;

    $aliProduct = new Product($bundle['_attributes']['TMall ID (Best Goods)'], $bundle['code']);

    $response = $aliProduct->setStock($stockMS[$bundle['_id']]['available'], $login);
}
$end = microtime(TRUE);
echo round(($end - $start), 2) . " seconds.";

telegram('Обновлен остаток для bestgoods time:'.round(($end - $start), 2), '-391758030');
