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


// Список складов для расчёта остатков

define('APPKEY', '27862248');
define('SECRET', 'ca6916e55a087b3561b5077fc8b83ee6');

define('LOGINS', array(
    array(
        'name' => 'Незабудка MR',
        'login' => 'NezabudkaMR@yandex.ru',
        'field_id' => '0bbcd991-81f4-11e9-9109-f8fc0004dec9',
        'sessionKey' => '50002500435yXPcbqwfU6Lmo2txf83nTdKufWRlABgs6PSv10e2657ccIUUwtbSdEo3',
        'cpCode' => 'UTV0a1NLakt5dE9DdzZOdEt1elhnblRnMURQaExvS0w4RVZEVHMyM2o2eTRqUjdiOEdxalpTVjhRN0ZBQldVZA==',
        'cnId' => '4398983084403'
    ),
    array(
        'name' => 'bestgoodsstore',
        'login' => 'bestgoodsstore@yandex.ru',
        'field_id' => '0bbcd3e6-81f4-11e9-9109-f8fc0004dec8',
        'sessionKey' => '50002301419cdaiudiQfUvkfkfueltW1bd07081FHiQGZdRtFEwBpxDxPrTG2jMrE6H',
        'cpCode' => 'QXJCQk1QcjJKTkZDbHk4ZVZ4bW11cFQ2L2QreW1XT0lJd2ZlMnEvL2dFZC9NbG5CSklEV2tiY0cxNkRSMWlYcQ==',
        'cnId' => '4398985192396'

    ),
    array(
        'name' => 'Новинки',
        'login' => 'novinkiooo@yandex.ru',
        'field_id' => 'e8a40577-77b9-11e9-912f-f3d40003d45d',
        'sessionKey' => '50002500501Vs15192a4fBdqLHT9iHrwvpCb0JydnTDyqj8eFwAOsukoFc4qg0Vxn2C',
        'cpCode' => 'OTQwTzB2T1U3N1Nza0Y3OVRKMHZyVWtPL0RFRjJHczBqUHBDRHBqK05LVXdBc1pJRkk0THo1YUVLR21PNE5IZQ==',
        'cnId' => '4398985964371'
    ),
    array(
        'name' => 'Незабудка iRobot',
        'login' => 'NezabudkaiRobot@yandex.ru',
        'field_id' => '0bbcde02-81f4-11e9-9109-f8fc0004deca',
        'sessionKey' => '50002500111c0AyTocCxSEw142e6ac49kntzvuDe7g0eIqC0sf6gjSBLVrYF8ZUBSSy',
        'cpCode' => 'czJBM3dFNm9aQ0RuSnhnY0tEK2p2a3g1cEI5aFYwSGw1TlpxTVAyUE1CYk1iYkRCTU1tWENocFo4alU3aFdmUg==',
        'cnId' => '4398985334183'
    ),
    array(
        'name' => 'Незабудка ND',
        'login' => 'NezabudkaND@yandex.ru',
        'field_id' => '0bbce15c-81f4-11e9-9109-f8fc0004decb',
        'sessionKey' => '50002701a33kuBupddegw9sTukkGPKzFsUBHbjPQDPTVU16922027tN2z5kWwXYAwTx',
        'cpCode' => 'V1ZDUlZnY09vbHoyQTFpNEZEUElkcGlmUE43Z1hYZEdoVEZwM2huTDlWeWVKUHdIUmY4QmFWV1FOdXVCT3JQeg==',
        'cnId' => '4398983195649'
    )
));


$stores = array(
    // 'f80cdf08-29a0-11e6-7a69-971100124ae8', // Склад-РЦ3
    // 'f257b41d-c2d9-11e7-6b01-4b1d00131678', // СКЛАД ХД
    '48de3b8e-8b84-11e9-9ff4-34e8001a4ea1',  // MP_NFF
);


// Telegram err logs integration
require_once '../class/telegram.php';

require_once '../ali_express/taobao/TopSdk.php';

require_once '../vendor/autoload.php';


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
        error_log(date("Y-m-d H:i:s", strtotime(gmdate("Y-m-d H:i:s")) + 3 * 60 * 60) . ' ' . $log_message . PHP_EOL, 3, $login['login'] . '.log');


    }

    return $syncErrors;
}

//report_stock_all
$stocks = new Stocks();
$stockMS = $stocks->getAll();

foreach (LOGINS as $login) {
    $start = 0;
    $start = microtime(TRUE);

    $products = $productsMS->findWithFieldTmall($login['login']);

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

    telegram('Обновлен остаток для ' . $login['name'], '-391758030');


}
