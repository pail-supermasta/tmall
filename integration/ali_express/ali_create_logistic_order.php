<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 07.09.2020
 * Time: 16:16
 */

require_once realpath(dirname(__FILE__) . '/..') . '/vendor/autoload.php';
require_once realpath(dirname(__FILE__) . '/..') . '/class/telegram.php';
require_once realpath(dirname(__FILE__) . '/..') . '/moi_sklad/ms_get_orders_dynamic.php';

require_once 'taobao/TopSdk.php';


use Avaks\MS\Orders;
use Avaks\MS\OrderMS;

/*define('APPKEY', '30833672');
define('SECRET', '1021396785b2eaa1497b7a58dddf19b3');*/


function getOrderShop($description)
{
    $sessionKey = '';
    $appkey = '';
    $secret = '';
    switch (true) {
        case  stripos($description, "BESTGOODS (ID 5041091)") !== false :
            $sessionKey = "50002301029r4LfaZzIzGMtdxRiWieuBmz1cGhQJ41a449a01ITXlFanTOWxJBdCZLQ5";
            $appkey = '32817975';
            $secret = 'fc3e140009f59832442d5c195c807fc0';
            break;
        case  stripos($description, "orion (ID 911725024)") !== false :
            $sessionKey = "50002201211qy8OzguEiR9T194d19ebvE7Girftw0dmHtGxmyX9d28OxEySXGK37wpOd";
            $appkey = '32817975';
            $secret = 'fc3e140009f59832442d5c195c807fc0';
            break;
        case stripos($description, "novinkiooo (ID 4901001)") !== false :
            $sessionKey = "50002500721djOAer7LBO3mQAZ5jVdhrE1730b9ebRsc7FeT4LrwHiIRmawEnCmZwzue";
            $appkey = '32817975';
            $secret = 'fc3e140009f59832442d5c195c807fc0';
            break;
    }
    return [$sessionKey, $appkey, $secret];
}

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

function findorderbyid($post_data, $sessionKey, $appkey, $secret)
{

    $c = new TopClient;
    $c->format = "json";
    $c->appkey = $appkey;
    $c->secretKey = $secret;
    $req = new AliexpressSolutionOrderInfoGetRequest;
    $param1 = new OrderDetailQuery;
    $param1->order_id = $post_data;
    $req->setParam1(json_encode($param1));
    $resp = $c->execute($req, $sessionKey);

    if (!isset($resp->result)) {
        return false;
    } else {
        $result = $resp->result->data;
        $res = json_encode((array)$result);
    }

    return $res;
}

function getLogisticOrderInfo($order, $logistics_order_id, $sessionKey, $appkey, $secret)
{

    $c = new TopClient;
    $c->format = "json";
    $c->appkey = $appkey;
    $c->secretKey = $secret;
    $req = new AliexpressLogisticsRedefiningGetonlinelogisticsinfoRequest;
    $req->setOrderId($order);
    $req->setLogisticsOrderId("$logistics_order_id");
    $resp = $c->execute($req, $sessionKey);

    return $resp;
}

function printInfo($internationallogistics_id, $sessionKey, $appkey, $secret)
{
    $c = new TopClient;
    $c->format = "json";
    $c->appkey = $appkey;
    $c->secretKey = $secret;
    $req = new AliexpressLogisticsRedefiningGetprintinfosRequest;
    $req->setPrintDetail("false");
    $warehouse_order_query_d_t_os = new AeopWarehouseOrderQueryPdfRequest;
//    $warehouse_order_query_d_t_os->id=123;
    $warehouse_order_query_d_t_os->international_logistics_id = "$internationallogistics_id";
    $req->setWarehouseOrderQueryDTOs(json_encode($warehouse_order_query_d_t_os));
    $resp = $c->execute($req, $sessionKey);
    return $resp;
}


/*CAINIAO*/
function firstMile($address_id, $solution_code, $sessionKey, $appkey, $secret)
{

    $c = new TopClient;
    $c->appkey = $appkey;
    $c->secretKey = $secret;
    $c->format = "json";

    $req = new CainiaoGlobalSolutionServiceResourceQueryRequest;
    $req->setLocale("ru_RU");
    $req->setSellerParam(json_encode(['code' => null]));
    $solution_service_res_param = new QuerySolutionServiceResParam;
    $solution_service_res_param->solution_code = "$solution_code";
    $service_param = new ServiceParam;
    $service_param->code = "DOOR_PICKUP";
    $solution_service_res_param->service_param = $service_param;
    $req->setSolutionServiceResParam(json_encode($solution_service_res_param));
    $sender_param = new OpenSenderParam;
    $sender_param->seller_address_id = $address_id;
    $req->setSenderParam(json_encode($sender_param));
    $resp = $c->execute($req, $sessionKey);

    return $resp;
}

function cainiaoGlobalLogisticOrderCreate($findorderbyidRes, $sender, $pickup, $refund, $logistics_type, $warehouse_code, $order, $sessionKey, $appkey, $secret)
{

    $orderAli = json_decode($findorderbyidRes, true);
    $zip = $orderAli['receipt_address']['zip'];
    $city = $orderAli['receipt_address']['city'];
    $province = $orderAli['receipt_address']['province'];
    $street = $orderAli['receipt_address']['detail_address'];
    $detail_address = $orderAli['receipt_address']['detail_address'];

    $c = new TopClient;
    $c->format = 'json';
    $c->appkey = $appkey;
    $c->secretKey = $secret;
    $req = new CainiaoGlobalLogisticOrderCreateRequest;
    $order_param = new OpenOrderParam;
    $trade_order_param = new OpenTradeOrderParam;
    $trade_order_param->trade_order_id = $order['name']; // order name
    $order_param->trade_order_param = $trade_order_param;// order name

    $solution_param = new OpenSolutionParam;
    $solution_param->solution_code = "$logistics_type"; // $logistics_type

    $service_params = new OpenServiceParam;
    $service_params->code = "DOOR_PICKUP";


    $features = new Features;
    $features->warehouse_code = "$warehouse_code";
    $service_params->features = $features;
    $solution_param->service_params = $service_params;
    $order_param->solution_param = $solution_param;


    $seller_info_param = new OpenSellerInfoParam;
    $order_param->seller_info_param = $seller_info_param;

    $sender_param = new OpenSenderParam;
    $sender_param->seller_address_id = "$sender";
    $order_param->sender_param = $sender_param;

    $returner_param = new OpenReturnerParam;
    $returner_param->seller_address_id = "$refund";
    $order_param->returner_param = $returner_param;

    $receiver_param = new ReceiverParam;
    $receiver_param->name = $order['_attributes']['ФИО'];
    $receiver_param->telephone = $order['_attributes']['Телефон'];
    $address_param = new OpenAddressParam;
    $address_param->zip_code = $zip;
    $address_param->country_name = "RU";
    $address_param->province = $province;
    $address_param->city = $city;
    $address_param->district = "";
    $address_param->street = $street;
    $address_param->detail_address = $detail_address;
    $address_param->country_code = "RU";
    $receiver_param->address_param = $address_param;
    $order_param->receiver_param = $receiver_param;

    $pickup_info_param = new OpenPickupInfoParam;
    $pickup_info_param->seller_address_id = "$pickup";
    $order_param->pickup_info_param = $pickup_info_param;

    $package_params = new OpenPackageParam;
    $orderAliproducts = $orderAli['child_order_list']['global_aeop_tp_child_order_dto'];


    foreach ($orderAliproducts as $orderAliproduct) {

        $price = 1;

        if (isset($orderAliproduct['product_price']['amount'])) {
            $price = (int)$orderAliproduct['product_price']['amount'];
        }

        $goodsNameEn = preg_replace("/[^а-яёa-z0-9. ]/iu", '', $orderAliproduct['product_name']);

        $item_params = new OpenItemParam;
        $item_params->item_id = $orderAliproduct['product_id'];
        $item_params->quantity = $orderAliproduct['product_count'] ?? 1;
        $item_params->english_name = $goodsNameEn;
        $item_params->local_name = $orderAliproduct['product_id'];
        $item_params->unit_price = $price;
        $item_params->sku = $orderAliproduct['sku_code'] ?? '';
        $item_params->weight = "1";
        $item_params->item_features = "cf_normal";
        $item_params->currency = "RUB";
        $item_params->total_price = $item_params->unit_price * $item_params->quantity;
        $item_params->length = "1";
        $item_params->width = "1";
        $item_params->height = "1";
        $package_params->item_params[] = $item_params;
    }

    $order_param->package_params = $package_params;
    $req->setOrderParam(json_encode($order_param));
    $req->setLocale("ru_RU");
    $resp = $c->execute($req, $sessionKey);

    return $resp;
}


/*MS*/

function setNewPositionPrice($findorderbyidRes, $order)
{

    /*get order Итого in MS*/

    $link = MS_PATH . "/entity/customerorder/?filter=name=" . $order . "&expand=positions";

    $findorderbyidRes = json_decode($findorderbyidRes, true);

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
        if (!empty($newPrice)) {
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

    foreach ($res['attributes'] as $attribute) {
        if ($attribute['id'] == '535dd809-1db1-11ea-0a80-04c00009d6bf') {
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
    var_dump($updateOrderResp);
    if (strpos($updateOrderResp, 'обработка-ошибок') > 0 || $updateOrderResp == '') {
        telegram("updateOrderResp error found " . $order, '-320614744');
        error_log(date("Y-m-d H:i:s", strtotime(gmdate("Y-m-d H:i:s")) + 3 * 60 * 60) . $updateOrderResp . " " . $order . PHP_EOL, 3, "updateOrderResp.log");

    }


}


// get orders from MS В работе

$ordersMSInstance = new Orders();
$ordersInWork = $ordersMSInstance->getOrdersInWork();
// for each MS ->
foreach ($ordersInWork as $orderInWork) {
    if ($orderInWork['name'] == '' || $orderInWork['name'] == ' ' || $orderInWork['name'] == 'M' || $orderInWork['name'] == '7') continue;

    /*1. Получение sessionKey в зависимости от описания заказа*/
    $getOrderShopArr = getOrderShop($orderInWork['description']);
    $sessionKey = $getOrderShopArr[0];
    $appkey = $getOrderShopArr[1];
    $secret = $getOrderShopArr[2];

    /*1.0 Получение списка адресов продавца, сохранённых в системе ++*/

    $getLogisticAddressesRes = getLogisticAddresses($sessionKey, $appkey, $secret);
    $sender = $getLogisticAddressesRes->sender_seller_address_list->senderselleraddresslist[0]->address_id ?? -1;
    $pickup = $getLogisticAddressesRes->pickup_seller_address_list->pickupselleraddresslist[0]->address_id ?? -1;
    $refund = $getLogisticAddressesRes->refund_seller_address_list->refundselleraddresslist[0]->address_id ?? -1;

    /*1.1. Получение деталей заказа из АЕ ++*/
    $findorderbyidRes = findorderbyid($orderInWork['name'], $sessionKey, $appkey, $secret);

    /*2. Получение списка логистических решений, доступных для отгрузки заказа ++*/

    $logistics_type = 'AE_RU_MP_COURIER_PH3';
    switch ($findorderbyidRes) {
        case strpos($findorderbyidRes, 'AE_RU_MP_PUDO_PH3') > 0:
            $logistics_type = 'AE_RU_MP_PUDO_PH3';
            break;
        case strpos($findorderbyidRes, 'AE_RU_MP_OVERSIZE_PH3') > 0:
            $logistics_type = 'AE_RU_MP_OVERSIZE_PH3';
            break;
        case strpos($findorderbyidRes, 'AE_RU_MP_RUPOST_PH3_FR') > 0:
            $logistics_type = 'AE_RU_MP_RUPOST_PH3_FR';
            break;
    }


    /*3. Получение списка кодов ресурсов, доступных для адреса отгрузки
     и выбранного режима первой мили ++*/

    $firstMileRes = firstMile($sender, $logistics_type, $sessionKey, $appkey, $secret);
    $warehouse_code = $firstMileRes->result->result->solution_service_res_list->solution_service_res_dto[0]->code;

    /*4. Создание логистического заказа ++ */

    $cainiaoGlobalLogisticOrderCreate = cainiaoGlobalLogisticOrderCreate($findorderbyidRes, $sender, $pickup, $refund, $logistics_type, $warehouse_code, $orderInWork, $sessionKey, $appkey, $secret);
    if (!isset($cainiaoGlobalLogisticOrderCreate->result->logistics_order_id)) {
        var_dump('logistics_order_id undefined');
        var_dump($cainiaoGlobalLogisticOrderCreate);
        $message = "Ошибка создания логистического заказа " . $orderInWork['name'];
        telegram($message, '-278688533');
        continue;
    }
    $logistics_order_id = $cainiaoGlobalLogisticOrderCreate->result->logistics_order_id;
    sleep(5);
    var_dump($logistics_order_id);
//    $logistics_order_id = 20130226187;
    $order = $orderInWork['name'];

    /*5. Скачивание данных логистического заказа ++*/

    $getLogisticOrderInfoRes = getLogisticOrderInfo($order, $logistics_order_id, $sessionKey, $appkey, $secret);
    if (isset($getLogisticOrderInfoRes->result_list->result[0])) {
        $lp_number = $getLogisticOrderInfoRes->result_list->result[0]->lp_number;

        if (!isset($getLogisticOrderInfoRes->result_list->result[0]->internationallogistics_id)) {
            sleep(5);
            $getLogisticOrderInfoRes = getLogisticOrderInfo($order, $logistics_order_id, $sessionKey, $appkey, $secret);

            if (!isset($getLogisticOrderInfoRes->result_list->result[0]->internationallogistics_id)) {
                sleep(5);
                $getLogisticOrderInfoRes = getLogisticOrderInfo($order, $logistics_order_id, $sessionKey, $appkey, $secret);

                if (!isset($getLogisticOrderInfoRes->result_list->result[0]->internationallogistics_id)) {
                    telegram("ОШИБКА!! получения наклейки $order", '-320614744', 'Markdown');
                    continue;
                }
            }
        }
        $internationallogistics_id = $getLogisticOrderInfoRes->result_list->result[0]->internationallogistics_id;
    }

    /*6. Получение PDF для печати этикетки заказа ++*/

    $printInfoResp = printInfo($internationallogistics_id, $sessionKey, $appkey, $secret);
    echo 'наклейка' . PHP_EOL;
    if (!isset(json_decode($printInfoResp->result)->body)) {
        sleep(5);
        $printInfoResp = printInfo($internationallogistics_id, $sessionKey, $appkey, $secret);
        if (!isset(json_decode($printInfoResp->result)->body)) {
            telegram("ОШИБКА!! получения наклейки $order", '-320614744', 'Markdown');
            continue;
        }
    }

    $b64 = json_decode($printInfoResp->result)->body;

    $bin = base64_decode($b64, true);

    file_put_contents("/home/tmall-service/public_html/integration/ali_express/files/labels/$order.pdf", $bin);


    /*ДОБАВИТЬ ТРЕК И LP В МС*/
    /*ДОБАВИТЬ PDF для печати В МС*/
    /*ОТГРУЗИТЬ В МС*/


    $orderMSInstance = new OrderMS();
    $orderMSInstance->id = $orderInWork['_id'];
    $orderMSInstance->name = $order;
    $orderMSInstance->logistics_type = $logistics_type;
    $orderMSInstance->description = $orderInWork['description'];
    $orderMSInstance->lpNumber = $lp_number;
    $orderMSInstance->trackNum = $internationallogistics_id;
    $setLogisticOrderResp = $orderMSInstance->setLogisticOrder();
    var_dump($setLogisticOrderResp);
    if (strpos($setLogisticOrderResp, 'обработка-ошибок') > 0 || $setLogisticOrderResp == '') {
        telegram("setToPack error found $orderMSInstance->name", '-320614744');
        error_log(date("Y-m-d H:i:s", strtotime(gmdate("Y-m-d H:i:s")) + 3 * 60 * 60) . $setLogisticOrderResp . " " . $orderMSInstance->name . PHP_EOL, 3, "setToPack.log");
    } else {
        $message = "Заказ №$orderMSInstance->name - оформлен в Цайняо и отправлен в Отгрузку";
        telegram($message, '-278688533');
        /*set new position price*/
        setNewPositionPrice($findorderbyidRes, $orderMSInstance->name);
    }


}



