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
        'sessionKey' => '50002501b14seTrdXDcT3OUujm14898f90lPG0lQtmh2nRxEtuysQNxvhfmWYdVTL6x',
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
        'sessionKey' => '50002501340seA1guGKsazsdfbDs5OwuiJHQi2FtXGja9pwjv7Jk14e8e956owYjFuf',
        'cpCode' => 'OTQwTzB2T1U3N1Nza0Y3OVRKMHZyVWtPL0RFRjJHczBqUHBDRHBqK05LVXdBc1pJRkk0THo1YUVLR21PNE5IZQ==',
        'cnId' => '4398985964371'
    ),
    array(
        'name' => 'Незабудка iRobot',
        'login' => 'NezabudkaiRobot@yandex.ru',
        'field_id' => '0bbcde02-81f4-11e9-9109-f8fc0004deca',
        'sessionKey' => '50002501310seA1gufJxhS1a47634fufZFgtCpVzlgkLHwGzwCC1HRwBxmrPG2jBtQh',
        'cpCode' => 'czJBM3dFNm9aQ0RuSnhnY0tEK2p2a3g1cEI5aFYwSGw1TlpxTVAyUE1CYk1iYkRCTU1tWENocFo4alU3aFdmUg==',
        'cnId' => '4398985334183'
    ),
    array(
        'name' => 'Незабудка ND',
        'login' => 'NezabudkaND@yandex.ru',
        'field_id' => '0bbce15c-81f4-11e9-9109-f8fc0004decb',
        'sessionKey' => '50002700f23CsXpqaf1l0ipRaztFcFeR5Mt1d9709d9YHGEvJ1IQXHD3CpkVzlo6zzy',
        'cpCode' => 'V1ZDUlZnY09vbHoyQTFpNEZEUElkcGlmUE43Z1hYZEdoVEZwM2huTDlWeWVKUHdIUmY4QmFWV1FOdXVCT3JQeg==',
        'cnId' => '4398983195649'
    )
));


$stores = array(
    // 'f80cdf08-29a0-11e6-7a69-971100124ae8', // Склад-РЦ3
    // 'f257b41d-c2d9-11e7-6b01-4b1d00131678', // СКЛАД ХД
    '48de3b8e-8b84-11e9-9ff4-34e8001a4ea1',  // MP_NFF
);


/*  INCLUDES    */

// Error handlers
//require_once '../class/error.php';

// Telegram err logs integration
require_once '../class/telegram.php';

require_once '../ali_express/taobao/TopSdk.php';

require_once '../vendor/autoload.php';


use Avaks\MS\Products;

$productsMS = new Products();
$syncErrors = '';

foreach (LOGINS as $login) {
    $start = 0;
    $start = microtime(TRUE);


    // Получаем из БД все товары, у которых поле TMall ID не пустое

    $products = $productsMS->getTmallProducts($login['field_id']);

    foreach ($products as $key => $product) {

        // if ($key > 2) continue;

        $product['attributes'] = json_decode($product['attributes'], true);
        // Получаем ID товара в Aliexpress
        $product['ali_product_id'] = $product['attributes'][$login['field_id']];
        if ($product['ali_product_id'] == '') continue;

        $skuCode = $product['code'];

        // Получаем сток из БД
        $stocks = $productsMS->getMsStock($product['id'], $stores);
        $product['stock'] = $stocks[0];

        $product['ali_stock'] = $product['stock'];


        if ($product['ali_stock'] < 0) $product['ali_stock'] = 0;

        $aliProduct = new \Avaks\AE\Product($product['ali_product_id'], $skuCode);

        $arr = $aliProduct->setStock($product['ali_stock'], $login);


        if ($arr == false) {
            $syncErrors .= 'ID Aliexp ' . $product['ali_product_id'] . ' Код МС ' . $product['code'] . ' ошибка сопоставления для магазина ' . $login['login'] . PHP_EOL;
            continue;
        } else {
            $product = array_merge($product, $arr);

            if ($product['new_stock'] === false) {
                $log_message = 'Tmall код МС ' . $product['ali_product_id'] . ' Код МС ' . $product['code'] . ' ' . $arr['old_stock'] . ' без изменений';
            } else {
                $log_message = 'Tmall код МС ' . $product['ali_product_id'] . ' Код МС ' . $product['code'] . ' Старое значение ' . $arr['old_stock'] . ' Новое значение ' . $product['ali_stock'];
                $product['new_stock'] = $product['ali_stock'];
            }

            $products[$key] = $product;
        }

        error_log(date("Y-m-d H:i:s", strtotime(gmdate("Y-m-d H:i:s")) + 3 * 60 * 60) . ' ' . $log_message . PHP_EOL, 3, $login['login'] . '.log');
    }

    $test = false;
    $message = "ЗАКОНЧИЛИСЬ товары для «{$login['name']}» на Aliexpress:";
    foreach ($products as $key => $product) {
        if (!isset($product['ali_product_id'])) continue;
        if ($product['new_stock'] == false) {
//            $message .= $product['name'] . "\n";
        } elseif ($product['ali_stock'] <= 0) {
            $test = true;
            $message .= $product['name'] . ' ' . $product['old_stock'] . ' => <b>' . $product['ali_stock'] . "</b>\n";
        }
    }
    /*if ($test == true) {
        telegram($message, '-391758030');
        echo $message;
    }*/


    error_log(json_encode($syncErrors), 3, 'syncErrors.log');
//    var_dump($syncErrors);
    $end = microtime(TRUE);

    telegram('Обновлен остаток для ' . $login['name'] . ' заняло ' . ($end - $start) . " seconds." , '-391758030');

//    echo $message;
}
