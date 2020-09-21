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

define('APPKEY', '27862248');
define('SECRET', 'ca6916e55a087b3561b5077fc8b83ee6');

$login = array(
    'name' => 'bestgoodsstore',
    'login' => 'bestgoodsstore@yandex.ru',
    'field_id' => '0bbcd3e6-81f4-11e9-9109-f8fc0004dec8',
    'sessionKey' => '50002301021q0OsaZzGzFoxi0th7ccSgM1add461cw0CJduh1FqvAlZmPkEo4jGYCQw',
    'cpCode' => 'QXJCQk1QcjJKTkZDbHk4ZVZ4bW11cFQ2L2QreW1XT0lJd2ZlMnEvL2dFZC9NbG5CSklEV2tiY0cxNkRSMWlYcQ==',
    'cnId' => '4398985192396'

);


$stores = array(
    '48de3b8e-8b84-11e9-9ff4-34e8001a4ea1',  // MP_NFF
);


// Telegram err logs integration
require_once realpath(dirname(__FILE__) . '/..') . '/class/telegram.php';

require_once realpath(dirname(__FILE__) . '/..') . '/ali_express/taobao/TopSdk.php';

require_once realpath(dirname(__FILE__) . '/..') . '/vendor/autoload.php';


use Avaks\MS\Products;
use Avaks\MS\Stocks;
use Avaks\AE\Product;
use Avaks\MS\Bundles;

$productsMS = new Products();
$bundlesMS = new Bundles();
$syncErrors = '';

function loggingRes($arr, $product, $syncErrors, $login)
{
    if ($arr == false) {
        $syncErrors .= 'ID Aliexp ' . $product['ali_product_id'] . ' Код МС ' . $product['code'] . ' ошибка сопоставления для магазина ' . $login['login'] . PHP_EOL;
    } else {


        if ($arr['new_stock'] == false && is_bool($arr['new_stock'])) {
            $log_message = 'Tmall код МС ' . $product['ali_product_id'] . ' Код МС ' . $product['code'] . ' ' . $arr['old_stock'] . ' без изменений';
        } else {
            $log_message = 'Tmall код МС ' . $product['ali_product_id'] . ' Код МС ' . $product['code'] . ' Старое значение ' . $arr['old_stock'] . ' Новое значение ' . $arr['new_stock'];
        }
//        error_log(date("Y-m-d H:i:s", strtotime(gmdate("Y-m-d H:i:s")) + 3 * 60 * 60) . ' ' . $log_message . PHP_EOL, 3, $login['login'] . '.log');


    }

    return $syncErrors;
}

//report_stock_all
$stocks = new Stocks();
$stockMS = $stocks->getAll();

$start = 0;
$start = microtime(TRUE);

$products = $productsMS->findWithFieldTmall($login['login']);

$duration = microtime(true) - $start;
echo $duration;
var_export($login);
var_export($products);
die();
foreach ($products as $key => $product) {

    // Получаем ID товара в Aliexpress
    $product['ali_product_id'] = $product['_attributes'][$productsMS->MSTmallFieldName];
    if ($product['ali_product_id'] == '') continue;

    $aliProduct = new Product($product['ali_product_id'], $product['code']);

    $response = $aliProduct->setStock($stockMS[$product['id']]['available'], $login);
    $syncErrors = loggingRes($response, $product, $syncErrors, $login);
}

$bundles = $bundlesMS->findWithFieldTmall($login['login']);

foreach ($bundles as $key => $bundle) {


    // Получаем ID товара в Aliexpress
    $bundle['ali_product_id'] = $bundle['_attributes'][$bundlesMS->MSTmallFieldName];
    if ($bundle['ali_product_id'] == '') continue;

    $aliProduct = new Product($bundle['ali_product_id'], $bundle['code']);
    $response = $aliProduct->setStock($stockMS[$bundle['id']]['available'], $login);

    $syncErrors = loggingRes($response, $bundle, $syncErrors, $login);


}

$duration = microtime(true) - $start;
echo $duration;
telegram('Обновлен остаток для ' . $login['name'], '-391758030');


