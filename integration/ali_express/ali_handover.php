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

define('APPKEY', '27862248');
define('SECRET', 'ca6916e55a087b3561b5077fc8b83ee6');


/*ALIEX*/

function getLogisticAddresses($sessionKey)
{
    $c = new TopClient;
    $c->format = "json";
    $c->appkey = APPKEY;
    $c->secretKey = SECRET;
    $req = new AliexpressLogisticsRedefiningGetlogisticsselleraddressesRequest;
    $req->setSellerAddressQuery('sender,pickup,refund');
    $resp = $c->execute($req, $sessionKey);

    return $resp;

}

function logisticsSellershipmentfortop($serviceName, $order, $logisticsNo, $sessionKey)
{
    $c = new TopClient;
    $c->format = 'json';
    $c->appkey = APPKEY;
    $c->secretKey = SECRET;
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
        $getLogisticAddressesRes = getLogisticAddresses($shopsOrders['sessionKey']);
        $shopsOrders['sender'] = $getLogisticAddressesRes->sender_seller_address_list->senderselleraddresslist[0]->address_id ?? -1;
        $shopsOrders['pickup'] = $getLogisticAddressesRes->pickup_seller_address_list->pickupselleraddresslist[0]->address_id ?? -1;
        $shopsOrders['refund'] = $getLogisticAddressesRes->refund_seller_address_list->refundselleraddresslist[0]->address_id ?? -1;
    }
    return $shopsOrders;
}


/*CAINIAO*/

function createHandoverList($refund, $pickup, $order_code_list, $sessionKey)
{

    $c = new TopClient;
    $c->appkey = APPKEY;
    $c->secretKey = SECRET;
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

function printHandoverList($sessionKey, $handoverContentId)
{

    /*1 - наклейка на контейнер*/
    /*512 - лист - используем его*/

    $c = new TopClient;
    $c->format = 'json';
    $c->appkey = APPKEY;
    $c->secretKey = SECRET;

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


// get orders from MS на выдаче
$ordersMSInstance = new Orders();
$ordersOnLoad = $ordersMSInstance->getOrdersOnLoad();

$shopsOrders = [];
foreach ($ordersOnLoad as $orderOnLoad) {


    switch (true) {
        case stripos($orderOnLoad['description'], "novinkiooo (ID 4901001)") !== false :
            $shopName = 'novinkiooo';
            $shopsOrders[$shopName]['sessionKey'] = "50002500c15tBJCowQScZ6julhS1dea01bfctShXhETAmyVfKkQKwey1lvK3ryD7z1t";
            $shopsOrders[$shopName] = buildShopOrders($orderOnLoad, $shopsOrders[$shopName]);
            break;
        case stripos($orderOnLoad['description'], "BESTGOODS (ID 5041091)") !== false :
            $shopName = 'BESTGOODS';
            $shopsOrders[$shopName]['sessionKey'] = "50002301021q0OsaZzGzFoxi0th7ccSgM1add461cw0CJduh1FqvAlZmPkEo4jGYCQw";
            $shopsOrders[$shopName] = buildShopOrders($orderOnLoad, $shopsOrders[$shopName]);
            break;
        case stripos($orderOnLoad['description'], "Noerden (ID 5012047)") !== false :
            $shopName = 'Noerden';
            $shopsOrders[$shopName]['sessionKey'] = "50002700811duwgr7oCt2sx1edacab6BZ6kWiLtBtQeBGBQfNxYCKlunzemnpZpP21d";
            $shopsOrders[$shopName] = buildShopOrders($orderOnLoad, $shopsOrders[$shopName]);

            break;
        case stripos($orderOnLoad['description'], "Morphy Richards (ID 5017058)") !== false :
            $shopName = 'Morphy Richards';
            $shopsOrders[$shopName]['sessionKey'] = "50002500501Vs151ac533BdqLFskhnQwtwheYk1CiSexTFfFAv6nWUefGArBboUuh8F";
            $shopsOrders[$shopName] = buildShopOrders($orderOnLoad, $shopsOrders[$shopName]);

            break;
        case stripos($orderOnLoad['description'], "iRobot (ID 5016030)") !== false :
            $shopName = 'iRobot';
            $shopsOrders[$shopName]['sessionKey'] = "50002500901sP1b7f673cFt7iCP3uQfb1JzgquDxxdddBqgtvwCkmwoWHooj7Cw4dCP";
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

        $createHandoverListResp = createHandoverList($shopOrders['refund'], $shopOrders['pickup'], $order_code_list, $shopOrders['sessionKey']);
        if (!isset($createHandoverListResp->result->data->handover_content_id)) {
            telegram("$key createHandoverList error found $order_code_list", '-320614744');
            var_export($createHandoverListResp);
            continue;
        } else {

            $handoverContentId = $createHandoverListResp->result->data->handover_content_id;

            /*8. Получение этикетки контейнера (паллеты) и листа передачи */

            $printHandoverListResp = printHandoverList($shopOrders['sessionKey'], $handoverContentId);
            echo 'лист передачи' . PHP_EOL;
            if (!isset($printHandoverListResp->result->data)) {
                sleep(5);
                $printHandoverListResp = printHandoverList($shopOrders['sessionKey'], $handoverContentId);
                if (!isset($printHandoverListResp->result->data)) {
                    telegram("ОШИБКА!! получения листа передачи $key", '-320614744', 'Markdown');
                    continue;
                }
            }
            $b64 = json_decode($printHandoverListResp->result->data)->body;
            $bin = base64_decode($b64, true);
            file_put_contents("/home/tmall-service/public_html/integration/ali_express/files/handover/$key" . "лист_передачи_$handoverContentId.pdf", $bin);
            $receptionLink = "https://tmall-service.a3w.ru/integration/ali_express/files/handover/$key" . "лист_передачи_$handoverContentId.pdf";
            $date = date('d-m-yy');
            telegramReception("TMALL Акт приема передачи отправлений $key [$date]($receptionLink)", '-385044014', 'Markdown');

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
                    case  stripos($shopOrder['description'], "AE_RU_MP_RUPOST_PH3") !== false :
                        $logistics_type = "AE_RU_MP_RUPOST_PH3";
                        break;
                }
                $sellerShipmentForTopResp = logisticsSellershipmentfortop($logistics_type, $shopOrder['name'], $shopOrder['_attributes']['Логистика: Трек'], $shopOrders['sessionKey']);
                var_dump($sellerShipmentForTopResp);
            }

        }
    }
}
