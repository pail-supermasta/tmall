<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 23.09.2019
 * Time: 11:42
 */


ini_set('display_errors', 1);

header('Content-Type: application/json');


use Avaks\MS\Orders;
use Avaks\MS\OrderMS;


require_once '../vendor/autoload.php';
require_once 'ali_order_details_dynamic.php';
require_once 'taobao/TopSdk.php';


define('APPKEY', '27862248');
define('SECRET', 'ca6916e55a087b3561b5077fc8b83ee6');
define('LOGINS', array(
    array(
        'name' => 'Незабудка MR',
        'login' => 'NezabudkaMR@yandex.ru',
        'field_id' => '0bbcd991-81f4-11e9-9109-f8fc0004dec9',
        'sessionKey' => '50002500500V1acb5e0esBdqLFskhnQwtwheYk1CiSexTFfFAv6nWUefGArBboUuh8F',
        'cpCode' => 'UTV0a1NLakt5dE9DdzZOdEt1elhnblRnMURQaExvS0w4RVZEVHMyM2o2eTRqUjdiOEdxalpTVjhRN0ZBQldVZA==',
        'cnId' => '4398983084403'
    ),
    array(
        'name' => 'bestgoodsstore',
        'login' => 'bestgoodsstore@yandex.ru',
        'field_id' => '0bbcd3e6-81f4-11e9-9109-f8fc0004dec8',
        'sessionKey' => '50002301103yrRc163e29d7ZzgUiKwh0xhbCbrgNTyjFHJHwjSsCd5lSPWxJBdCZLQ5',
        'cpCode' => 'QXJCQk1QcjJKTkZDbHk4ZVZ4bW11cFQ2L2QreW1XT0lJd2ZlMnEvL2dFZC9NbG5CSklEV2tiY0cxNkRSMWlYcQ==',
        'cnId' => '4398985192396'

    ),
    array(
        'name' => 'Новинки',
        'login' => 'novinkiooo@yandex.ru',
        'field_id' => 'e8a40577-77b9-11e9-912f-f3d40003d45d',
        'sessionKey' => '50002500737cfRNerkKCo3qqiYbivGmqfVSjYiCsiryXdglSI1e4adc70E1lvJFkJus',
        'cpCode' => 'OTQwTzB2T1U3N1Nza0Y3OVRKMHZyVWtPL0RFRjJHczBqUHBDRHBqK05LVXdBc1pJRkk0THo1YUVLR21PNE5IZQ==',
        'cnId' => '4398985964371'
    ),
    array(
        'name' => 'Незабудка iRobot',
        'login' => 'NezabudkaiRobot@yandex.ru',
        'field_id' => '0bbcde02-81f4-11e9-9109-f8fc0004deca',
        'sessionKey' => '50002500703cfRN14e0f429er9JHvWsscC4j2hLvisve7cDy7nRylIluKtZQNPQCxQu',
        'cpCode' => 'czJBM3dFNm9aQ0RuSnhnY0tEK2p2a3g1cEI5aFYwSGw1TlpxTVAyUE1CYk1iYkRCTU1tWENocFo4alU3aFdmUg==',
        'cnId' => '4398985334183'
    ),
    array(
        'name' => 'Незабудка ND',
        'login' => 'NezabudkaND@yandex.ru',
        'field_id' => '0bbce15c-81f4-11e9-9109-f8fc0004decb',
        'sessionKey' => '50002700532VsBdqLktCoFvXnxeB4JxEjRbWTD7DDviK14d17745W1DmhKcqJnlQq9B',
        'cpCode' => 'V1ZDUlZnY09vbHoyQTFpNEZEUElkcGlmUE43Z1hYZEdoVEZwM2huTDlWeWVKUHdIUmY4QmFWV1FOdXVCT3JQeg==',
        'cnId' => '4398983195649'
    )
));


$orders = new Orders();
$ordersNoSticker = $orders->getOrdersNoSticker();


function printCainiaoSticker(OrderMS $orderMS, $sessionKey)
{


    $customs = new \Avaks\Customs();
    $findorderbyidRes = findorderbyid($orderMS->name, $sessionKey);


    $productShortener = $findorderbyidRes['child_order_ext_info_list']['global_aeop_tp_order_product_info_dto'];
    $productDescriptions = array();
    foreach ($productShortener as $product) {
        $productDescriptions[] = $product['product_name'];
    }


//    $mailNo = 'AEWH0000657988RU4';
    $mailNo = 'AEWH0000' . $orderMS->trackNum . 'RU4';

    $address = $findorderbyidRes['receipt_address'];
    $country = $address['country'] == 'RU' ? 'РФ' : $address['country'];
    $address2 = isset($address['address2']) ? $address['address2'] : '';
    $fullAddress = $address['zip'] . ", " . $country . ", " . $address['province'] . ", " . $address['city'] . ", " . $address['detail_address'] . ", " . $address2;
    $receiverName = $address['contact_person'];
    $receiverPhone = $address['mobile_no'];
    $otgruzkaDate = date('Y-m-d', strtotime($findorderbyidRes['gmt_create']));
//    $lpNum = 'LP00141041126618';
    $lpNum = 'LP00' . $orderMS->lpNumber;


    $toPrint = array('productDescriptions' => $productDescriptions,
        'fullAddress' => $fullAddress,
        'receiverName' => $receiverName,
        'receiverPhone' => $receiverPhone,
        'mailNo' => $mailNo,
        'otgruzkaDate' => $otgruzkaDate,
        'LPNum' => $lpNum,
        'AEOrderId' => $orderMS->name);

    $pdfCode = $customs->generateCainiaoSticker($toPrint);

    $content = base64_encode($pdfCode);
    $attribute['id'] = 'b8a8f6d6-5782-11e8-9ff4-34e800181bf6';
    $attribute['file']['filename'] = "Маркировка $orderMS->name.pdf";
    $attribute['file']['content'] = $content;
    $put_data['attributes'][] = $attribute;

    $final = json_encode($put_data);

    return $orderMS->setSticker($final);


}


function getOrderShop($attributes)
{
    $sessionKey = '';
    switch (true) {
        case stripos($attributes, "novinkiooo (ID 4901001)") !== false :
            $sessionKey = '50002500737cfRNerkKCo3qqiYbivGmqfVSjYiCsiryXdglSI1e4adc70E1lvJFkJus';
            break;
        case  stripos($attributes, "BESTGOODS (ID 5041091)") !== false :
            $sessionKey = "50002301103yrRc163e29d7ZzgUiKwh0xhbCbrgNTyjFHJHwjSsCd5lSPWxJBdCZLQ5";
            break;
        case  stripos($attributes, "Noerden (ID 5012047)") !== false :
            $sessionKey = "50002700532VsBdqLktCoFvXnxeB4JxEjRbWTD7DDviK14d17745W1DmhKcqJnlQq9B";
            break;
        case  stripos($attributes, "Morphy Richards (ID 5017058)") !== false :
            $sessionKey = "50002500500V1acb5e0esBdqLFskhnQwtwheYk1CiSexTFfFAv6nWUefGArBboUuh8F";
            break;
        case  stripos($attributes, "iRobot (ID 5016030)") !== false :
            $sessionKey = "50002500703cfRN14e0f429er9JHvWsscC4j2hLvisve7cDy7nRylIluKtZQNPQCxQu";
            break;
    }
    return $sessionKey;
}

foreach ($ordersNoSticker as $orderNoSticker) {

    $orderMS = new OrderMS($orderNoSticker['id'], $orderNoSticker['name']);
    $orderMS->lpNumber = $orderNoSticker['externalCode'];
    $orderMS->trackNum = json_decode($orderNoSticker['attributes'], true)['a446677c-46b8-11e7-7a34-5acf0031d7b9'];
    $sessionKey = getOrderShop($orderNoSticker['description']);

    var_dump(printCainiaoSticker($orderMS, $sessionKey));


}















/*foreach (LOGINS as $credential) {
    formMasterList($credential);
}*/


/*get orders from MS with no sticker but with track num and related to Cainiao*/

/*assemble sticker details*/

