<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 07.09.2020
 * Time: 16:25
 */


require_once realpath(dirname(__FILE__) . '/..') . '/vendor/autoload.php';
require_once realpath(dirname(__FILE__) . '/..') . '/class/telegram.php';

require_once 'taobao/TopSdk.php';

use Avaks\MS\Orders;

/*define('APPKEY', '30833672');
define('SECRET', '1021396785b2eaa1497b7a58dddf19b3');*/


/*ALIEX*/

function getLogisticAddresses($sessionKey, $appkey, $secret)
{
    $c = new TopClient;
    $c->format = "json";
    $c->appkey = $appkey;
    $c->secretKey = $secret;
    $req = new AliexpressLogisticsRedefiningGetlogisticsselleraddressesRequest;
    $req->setSellerAddressQuery('sender,pickup,refund');
    $resp = $c->execute($req, $sessionKey);

    return $resp;

}

function logisticsSellershipmentfortop($serviceName, $order, $logisticsNo, $sessionKey, $appkey, $secret)
{
    $c = new TopClient;
    $c->format = 'json';
    $c->appkey = $appkey;
    $c->secretKey = $secret;
    $req = new AliexpressLogisticsSellershipmentfortopRequest;
    $req->setLogisticsNo("$logisticsNo");
    $req->setSendType("all");
    $req->setOutRef("$order");
    $req->setTrackingWebsite("https://global.cainiao.com/");
    $req->setServiceName("$serviceName");
//    $req->setServiceName("AE_RU_MP_COURIER_PH3_CITY");
    $resp = $c->execute($req, $sessionKey);
    return $resp;
}

// helper
function buildShopOrders($orderOnLoad, $shopsOrders)
{

    $shopsOrders['orders'][] = $orderOnLoad;
    if (count($shopsOrders['orders']) > 0 && !isset($shopsOrders['sender'])) {
        $getLogisticAddressesRes = getLogisticAddresses($shopsOrders['sessionKey'], $shopsOrders['appkey'], $shopsOrders['secret']);
        $shopsOrders['sender'] = $getLogisticAddressesRes->sender_seller_address_list->senderselleraddresslist[0]->address_id ?? -1;
        $shopsOrders['pickup'] = $getLogisticAddressesRes->pickup_seller_address_list->pickupselleraddresslist[0]->address_id ?? -1;
        $shopsOrders['refund'] = $getLogisticAddressesRes->refund_seller_address_list->refundselleraddresslist[0]->address_id ?? -1;
    }
    return $shopsOrders;
}


/*CAINIAO*/

function createHandoverList($refund, $pickup, $order_code_list, $sessionKey, $appkey, $secret)
{

    $c = new TopClient;
    $c->appkey = $appkey;
    $c->secretKey = $secret;
    $c->format = "json";

    $req = new CainiaoGlobalHandoverCommitRequest;
    $user_info = new UserInfoDto;
    $req->setUserInfo(json_encode($user_info));
    $return_info = new ReturnerDto;
    $return_info->address_id = "$refund";
    $req->setReturnInfo(json_encode($return_info));
    $pickup_info = new PickupDto;
    $address = new AddressDto;
    $pickup_info->address_id = "$pickup";
    $req->setPickupInfo(json_encode($pickup_info));
    $req->setClient("ISV-aliekspress_kompaniya");

    $req->setOrderCodeList($order_code_list);

    $req->setLocale("ru_RU");
    $resp = $c->execute($req, $sessionKey);

    return $resp;
}

function printHandoverList($sessionKey, $appkey, $secret, $handoverContentId)
{

    /*1 - наклейка на контейнер*/
    /*512 - лист - используем его*/

    $c = new TopClient;
    $c->format = 'json';
    $c->appkey = $appkey;
    $c->secretKey = $secret;

    $req = new CainiaoGlobalHandoverPdfGetRequest;
    $user_info = new UserInfoDto;
    $req->setUserInfo(json_encode($user_info));
    $req->setClient("ISV-aliekspress_kompaniya");
    $req->setHandoverContentId("$handoverContentId");
    $req->setType("512");
    $req->setLocale("ru_RU");
    $resp = $c->execute($req, $sessionKey);

    return $resp;
}

function printHandoverSticker($sessionKey, $appkey, $secret, $handoverContentId)
{

    /*1 - наклейка на контейнер*/
    /*512 - лист - используем его*/

    $c = new TopClient;
    $c->format = 'json';
    $c->appkey = $appkey;
    $c->secretKey = $secret;

    $req = new CainiaoGlobalHandoverPdfGetRequest;
    $user_info = new UserInfoDto;
    $req->setUserInfo(json_encode($user_info));
    $req->setClient("ISV-aliekspress_kompaniya");
    $req->setHandoverContentId("$handoverContentId");
    $req->setType("1");
    $req->setLocale("ru_RU");
    $resp = $c->execute($req, $sessionKey);

    return $resp;
}


// get orders from MS на выдаче
$ordersMSInstance = new Orders();
$ordersOnLoad = $ordersMSInstance->getOrdersOnLoad();

$shopsOrders = [];
foreach ($ordersOnLoad as $orderOnLoad) {
//    if (strpos($orderOnLoad['description'], '«Добавлен в акт') > 0) continue;
    switch (true) {
        case stripos($orderOnLoad['description'], "BESTGOODS (ID 5041091)") !== false :
            $shopName = 'BESTGOODS';
            $shopsOrders[$shopName]['sessionKey'] = "50002301029r4LfaZzIzGMtdxRiWieuBmz1cGhQJ41a449a01ITXlFanTOWxJBdCZLQ5";
            $shopsOrders[$shopName]['appkey'] = "32817975";
            $shopsOrders[$shopName]['secret'] = "fc3e140009f59832442d5c195c807fc0";
            $shopsOrders[$shopName] = buildShopOrders($orderOnLoad, $shopsOrders[$shopName]);
            break;
        case stripos($orderOnLoad['description'], "orion (ID 911725024)") !== false :
            $shopName = 'orion';
            $shopsOrders[$shopName]['sessionKey'] = "50002201211qy8OzguEiR9T194d19ebvE7Girftw0dmHtGxmyX9d28OxEySXGK37wpOd";
            $shopsOrders[$shopName]['appkey'] = "32817975";
            $shopsOrders[$shopName]['secret'] = "fc3e140009f59832442d5c195c807fc0";
            $shopsOrders[$shopName] = buildShopOrders($orderOnLoad, $shopsOrders[$shopName]);

            break;

    }


}

// for each SHOP
foreach ($shopsOrders as $key => $shopOrders) {
    if (count($shopOrders['orders']) > 0) {

        echo $key . PHP_EOL;
        $order_code_list = '';
        foreach ($shopOrders['orders'] as $index => $shopOrder) {
            if ($index + 1 == count($shopOrders['orders'])) {
                $order_code_list .= $shopOrder['externalCode'];
            } else {
                $order_code_list .= $shopOrder['externalCode'] . ",";
            }

        }

        $createHandoverListResp = createHandoverList($shopOrders['refund'], $shopOrders['pickup'], $order_code_list, $shopOrders['sessionKey'], $shopOrders['appkey'], $shopOrders['secret']);
        if (!isset($createHandoverListResp->result->data->handover_content_id)) {
            var_export($createHandoverListResp);
            $sub_msg = ((array)$createHandoverListResp)['sub_msg'];
            telegram("$key createHandoverList error found $order_code_list", '-320614744');
            telegramReception("ТМОЛ. Ошибка создания листа передачи. ", '-385044014');


            preg_match_all(
                '~(?<=LP).+?(?= )~',
                $sub_msg,
                $matches
            );


            $externalCode = "LP" . $matches[0][0];
            foreach ($shopOrders['orders'] as $index => $shopOrder) {
                if ($shopOrder['externalCode'] === $externalCode) {
                    echo $shopOrder['name'];
                    telegramReception("ТМОЛ. Для заказа " . $shopOrder['name'] . " уже есть лист передачи. Переведите его в статус Доставляется и перейдите по [ссылке](http://tmall-service.a3w.ru/integration/ali_express/ali_handover.php)", '-385044014', 'Markdown', true);

                    break;
                }
            }

            continue;
        }
        else {

            $handoverContentId = $createHandoverListResp->result->data->handover_content_id;

            /*8.1. Получение листа передачи */

            $printHandoverListResp = printHandoverList($shopOrders['sessionKey'], $shopOrders['appkey'], $shopOrders['secret'], $handoverContentId);
            echo 'лист передачи' . PHP_EOL;
            if (!isset($printHandoverListResp->result->data)) {
                sleep(15);
                $printHandoverListResp = printHandoverList($shopOrders['sessionKey'], $shopOrders['appkey'], $shopOrders['secret'], $handoverContentId);
                if (!isset($printHandoverListResp->result->data)) {
                    sleep(15);
                    $printHandoverListResp = printHandoverList($shopOrders['sessionKey'], $shopOrders['appkey'], $shopOrders['secret'], $handoverContentId);
                    if (!isset($printHandoverListResp->result->data)) {
                        telegram("ОШИБКА!! получения листа передачи $key", '-320614744', 'Markdown');
                        die();
                    }
                }
            }
            $b64 = json_decode($printHandoverListResp->result->data)->body;
            $bin = base64_decode($b64, true);
            file_put_contents("/home/tmall-service/public_html/integration/ali_express/files/handover/$key" . "лист_передачи_$handoverContentId.pdf", $bin);
            $receptionLink = "https://tmall-service.a3w.ru/integration/ali_express/files/handover/$key" . "лист_передачи_$handoverContentId.pdf";
            $date = date('d-m-y');
            $ordersSent = sizeof($shopOrders['orders']);
            telegramReception("TMALL Акт приема передачи $ordersSent отправлений $key [$date]($receptionLink)", '-385044014', 'Markdown');

            /*8.2. Получение этикетки контейнера (паллеты)*/

            $printHandoverStickerResp = printHandoverSticker($shopOrders['sessionKey'], $shopOrders['appkey'], $shopOrders['secret'], $handoverContentId);
            echo 'этикетка контейнера' . PHP_EOL;
            if (!isset($printHandoverStickerResp->result->data)) {
                sleep(15);
                $printHandoverStickerResp = printHandoverSticker($shopOrders['sessionKey'], $shopOrders['appkey'], $shopOrders['secret'], $handoverContentId);
                if (!isset($printHandoverStickerResp->result->data)) {
                    sleep(15);
                    $printHandoverStickerResp = printHandoverSticker($shopOrders['sessionKey'], $shopOrders['appkey'], $shopOrders['secret'], $handoverContentId);
                    if (!isset($printHandoverStickerResp->result->data)) {
                        telegram("ОШИБКА!! получения этикетки контейнера $key", '-320614744', 'Markdown');
                        die();
                    }
                }
            }
            $b64 = json_decode($printHandoverStickerResp->result->data)->body;
            $bin = base64_decode($b64, true);
            file_put_contents("/home/tmall-service/public_html/integration/ali_express/files/handover/$key" . "этикетка_контейнера_$handoverContentId.pdf", $bin);
            $receptionLink = "https://tmall-service.a3w.ru/integration/ali_express/files/handover/$key" . "этикетка_контейнера_$handoverContentId.pdf";
            $date = date('d-m-y');
            $ordersSent = sizeof($shopOrders['orders']);
            telegramReception("TMALL Этикетка контейнера $ordersSent отправлений $key [$date]($receptionLink)", '-385044014', 'Markdown');

            $trackNumsUnset = 0;
            $failedOrders = "";
            foreach ($shopOrders['orders'] as $shopOrder) {

                /*9. Отметка заказа как отгруженного в системе AliExpress*/

                switch (true) {
                    case stripos($shopOrder['description'], "AE_RU_MP_COURIER_PH3") !== false :
                        $logistics_type = "AE_RU_MP_COURIER_PH3_CITY";
                        break;
                    case  stripos($shopOrder['description'], "AE_RU_MP_PUDO_PH3") !== false :
                        $logistics_type = "AE_RU_MP_PUDO_PH3";
                        break;
                    case  stripos($shopOrder['description'], "AE_RU_MP_OVERSIZE_PH3") !== false :
                        $logistics_type = "AE_RU_MP_OVERSIZE_PH3";
                        break;
                    case  stripos($shopOrder['description'], "AE_RU_MP_RUPOST_PH3_FR") !== false :
                        $logistics_type = "AE_RU_MP_RUPOST_PH3_FR";
                        break;
                }
                $sellerShipmentForTopResp = logisticsSellershipmentfortop($logistics_type, $shopOrder['name'], $shopOrder['_attributes']['Логистика: Трек'], $shopOrders['sessionKey'], $shopOrders['appkey'], $shopOrders['secret']);
//                var_dump($sellerShipmentForTopResp);
                if ($sellerShipmentForTopResp->result_success === false) {
                    $failedOrders .= $shopOrder['name'] . " ";
                }
            }
            if (strlen($failedOrders) > 1) {
                telegramReception("TMALL. Ошибки установки трек номеров для заказов $failedOrders", '-385044014');
            }
            setHasListMakerForOrdersInMS($shopOrders['orders'], " «Добавлен в акт $handoverContentId »", $failedOrders);

        }
    }
}


function setHasListMakerForOrdersInMS($shopOrders, $listNumber, $failedOrders)
{

    foreach ($shopOrders as $shopOrder) {
        if (strpos($failedOrders, $shopOrder['name']) > 0) continue;
        /*update comment*/
        $oldDescription = $shopOrder['description'];
        var_dump("setHasListMakerForOrdersInMS processing " . $shopOrder['name']);

        $postdata = '{"description": "' . $oldDescription . $listNumber . '"}';
        $orderMSInstance = new \Avaks\MS\OrderMS($shopOrder['id']);

        $updateOrderResp = $orderMSInstance->updateOrder($postdata);
        if (strpos($updateOrderResp, 'обработка-ошибок') > 0 || $updateOrderResp == '') {
            telegram("setHasListMakerForOrdersInMS error found " . $shopOrder['name'], '-320614744');
            error_log(date("Y-m-d H:i:s", strtotime(gmdate("Y-m-d H:i:s")) + 3 * 60 * 60) . $updateOrderResp . " " . $shopOrder['name'] . PHP_EOL, 3, "setHasListMakerForOrdersInMS.log");
        }
    }

}