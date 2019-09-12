<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 09.09.2019
 * Time: 16:32
 */

error_reporting(E_ALL);
ini_set("display_errors", 1);



// Список складов для расчёта остатков

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


define('WHs', array(
    // 'f80cdf08-29a0-11e6-7a69-971100124ae8', // Склад-РЦ3
    // 'f257b41d-c2d9-11e7-6b01-4b1d00131678', // СКЛАД ХД
    '48de3b8e-8b84-11e9-9ff4-34e8001a4ea1',  // MP_NFF
));


/*  INCLUDES    */

// Error handlers
require_once '../class/error.php';

// Telegram err logs integration
require_once '../class/telegram.php';

// Cancel order in MS
require_once '../moi_sklad/ms_change_order_dynamic.php';

require_once '../ali_express/taobao/TopSdk.php';

require_once '../vendor/autoload.php';




use Avaks\MS\Products;
$products = new Products();


foreach (LOGINS as $login) {


    // Получаем из БД все товары, у которых поле TMall ID не пустое

    $products = $products->getTmallProducts($login['field_id']);
    var_dump(sizeof($products));
    die();

    foreach ($products as $key => $product) {

        // if ($key > 2) continue;

        $product['attributes'] = json_decode($product['attributes'], true);
        // Получаем ID товара в Aliexpress
        $product['ali_product_id'] = $product['attributes'][$login['field_id']];
        if ($product['ali_product_id'] == '') continue;

        // Получаем сток из БД
        $product['stock'] = getMsStock($product['id'], $stores);
        $product['ali_stock'] = $product['stock'];
        // Ограничиваем максимальный сток 5 штуками
//		if ($product['ali_stock'] > 5) $product['ali_stock'] = 5;
        if ($product['ali_stock'] < 0) $product['ali_stock'] = 0;

        $arr = ali_setProductStock($product['ali_product_id'], $product['ali_stock'], $login);

        if (!$arr) {
            var_dump($product['name'] . ' неверный ID aliexpress для ' . $login['name']);
            continue;
        }

        $product = array_merge($product, $arr);

        if ($product['new_stock'] === false) {
            var_dump($product['ali_product_id'] . ' ' . $product['code'] . ' ' . round(100 * ($key / count($products))) . '% ' . $product['name'] . ' ' . $product['old_stock'] . ' без изменений');
        } else {
            var_dump($product['ali_product_id'] . ' ' . $product['code'] . ' ' . round(100 * ($key / count($products))) . '% ' . $product['name'] . ' ' . $product['old_stock'] . ' ' . $product['new_stock']);
        }

        $products[$key] = $product;
    }

    $test = false;
    $message = "Обновление стока «{$login['name']}» на Aliexpress:";
    foreach ($products as $key => $product) {
        // if ($key > 2) continue;
        if (!isset($product['ali_product_id'])) continue;
        if ($product['new_stock'] === false) {
            // $message.= $product['name']."\n";
        } else {
            $test = true;
            $message .= $product['name'] . ' ' . $product['old_stock'] . ' => <b>' . $product['new_stock'] . "</b>\n";
        }
    }

    if (!$test) {
        $message .= " без изменений<br>";
    }

    var_dump(telegram($message));

    echo $message;
}
