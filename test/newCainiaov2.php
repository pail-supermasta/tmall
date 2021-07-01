<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 31.08.2020
 * Time: 11:23
 */

ini_set('display_errors', 1);

header('Content-Type: application/json; charset=utf-8;');


require_once '../integration/vendor/autoload.php';
require_once '../integration/ali_express/taobao/TopSdk.php';


define('APPKEY', '27862248');
define('SECRET', 'ca6916e55a087b3561b5077fc8b83ee6');

//$order = '5005298761744525'; //pudo
//$order = '5005281069484525'; //russian post
$order = '8117797642703308'; //courier
$sessionKey = '50002300d289mlqwnqAebm1lqueSpe9ECuAKVtDg1b6f0174jvgyezVnDsmJ4OkbLKm';

/*ALIEX*/

function findorderbyid($post_data, $sessionKey)
{

    $c = new TopClient;
    $c->format = "json";
    $c->appkey = APPKEY;
    $c->secretKey = SECRET;
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

function getLogisticOrderInfo($order, $logistics_order_id, $sessionKey)
{

    $c = new TopClient;
    $c->format = "json";
    $c->appkey = APPKEY;
    $c->secretKey = SECRET;
    $req = new AliexpressLogisticsRedefiningGetonlinelogisticsinfoRequest;
//    $req->setChinaLogisticsId("LA234234CN");
//    $req->setInternationalLogisticsId("AEWH0001761666RU4");
//    $req->setLogisticsStatus("CLOSED");
//    $req->setGmtCreateEndStr("2016-06-27 18:15:00");
//    $req->setPageSize("10");
//    $req->setQueryExpressOrder("false");
//    $req->setCurrentPage("1");
    $req->setOrderId($order);
//    $req->setGmtCreateStartStr("2016-06-27 18:20:00");
    $req->setLogisticsOrderId("$logistics_order_id");
    $resp = $c->execute($req, $sessionKey);

    return $resp;
}

function printInfo($internationallogistics_id, $sessionKey)
{
    $c = new TopClient;
    $c->format = "json";
    $c->appkey = APPKEY;
    $c->secretKey = SECRET;
    $req = new AliexpressLogisticsRedefiningGetprintinfosRequest;
    $req->setPrintDetail("false");
    $warehouse_order_query_d_t_os = new AeopWarehouseOrderQueryPdfRequest;
//    $warehouse_order_query_d_t_os->id=123;
    $warehouse_order_query_d_t_os->international_logistics_id = "$internationallogistics_id";
    $req->setWarehouseOrderQueryDTOs(json_encode($warehouse_order_query_d_t_os));
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


/*CAINIAO*/

function cainiaoGlobalSolutionInquiry($order, $sessionKey)
{

    $c = new TopClient;
    $c->format = 'json';
    $c->appkey = APPKEY;
    $c->secretKey = SECRET;
    $req = new CainiaoGlobalSolutionInquiryRequest;
    $req->setLocale("ru_RU");
    $req->setTradeOrderParam('{"trade_order_id" : ' . $order . '}');
    $req->setSellerInfoParam('{}');
    $req->setPackageParams('{
        "length":1,
        "width":1,
        "height":1,
        "weight":2
    }');
    $resp = $c->execute($req, $sessionKey);

    return $resp;
}

function cainiaoGlobalLogisticOrderCreate($findorderbyidRes, $sender, $pickup, $refund, $logistics_type, $warehouse_code, $order, $sessionKey)
{

    /*$c = new TopClient;
    $c->format = 'json';
    $c->appkey = APPKEY;
    $c->secretKey = SECRET;
    $req = new CainiaoGlobalLogisticOrderCreateRequest;
    $order_param = new OpenOrderParam;//
    $trade_order_param = new OpenTradeOrderParam;//
    $trade_order_param->trade_order_id = "$order"; // order name
    $order_param->trade_order_param = $trade_order_param;// order name

    $solution_param = new OpenSolutionParam;//
    $solution_param->solution_code = "$logistics_type"; // $logistics_type

    $service_params = new OpenServiceParam;//
    $service_params->code = "DOOR_PICKUP";//++


    $features = new Features;
    $features->warehouse_code = "$warehouse_code";//
//    $features->service_resource_code="CAINIAO_STANDARD_30228135";
//    $features->express_mail_no="LBxxxx001";
//    $features->express_company_id="100";
//    $features->express_company_name="顺丰快递";
    $service_params->features = $features;//
    $solution_param->service_params = $service_params;
    $order_param->solution_param = $solution_param;//


    $seller_info_param = new OpenSellerInfoParam;
//    $seller_info_param->top_user_key="1343";
    $order_param->seller_info_param = $seller_info_param;

    $sender_param = new OpenSenderParam;//
    $sender_param->seller_address_id = "$sender";//
    $order_param->sender_param = $sender_param;//

    $returner_param = new OpenReturnerParam;//
    $returner_param->seller_address_id = "$refund";//
    $order_param->returner_param = $returner_param;//

    $receiver_param = new ReceiverParam;//
    $receiver_param->name = "Муранов Вячеслав Викторович";//++
    $receiver_param->telephone = "+79032224457";//++
//    $receiver_param->mobile_phone="435433";
    $address_param = new OpenAddressParam;
//    $address_param->division_id="124";
    $address_param->zip_code = "129626";// ++
    $address_param->country_name = "RU";//++
    $address_param->province = "Северо-Восточный административный округ";//++
    $address_param->city = "Moscow";//++
    $address_param->district = "";//++
    $address_param->street = "улица Космонавтов";//++
    $address_param->detail_address = "улица Космонавтов, 24";//++
    $address_param->country_code = "RU";//++
    $receiver_param->address_param = $address_param;//
//    $receiver_param->user_nick="cnxxx";
//    $receiver_param->email="test@alibaba-inc.com";
    $order_param->receiver_param = $receiver_param;//

    $pickup_info_param = new OpenPickupInfoParam;//
    $pickup_info_param->seller_address_id = "$pickup";//
    $order_param->pickup_info_param = $pickup_info_param;//

    $package_params = new OpenPackageParam;
//    $package_params->length = "12";
//    $package_params->width = "324";
//    $package_params->height = "12";
//    $package_params->weight = "23";
//    $package_params->price = "12";
    $item_params = new OpenItemParam;
    $item_params->item_id = "4000322322571";//
    $item_params->quantity = "1";//
    $item_params->english_name = "Utyug Parogenerator Morphy Richards Saturn Intellitemp 305003 Utyug-Parogenerator";//
    $item_params->local_name = "4000322322571";//
    $item_params->unit_price = "7850";//++
    $item_params->sku = "5011832067364";//++
//    $item_params->sc_item_id = "1";
    $item_params->weight = "1";//
    $item_params->item_features = "cf_normal";//
    $item_params->currency = "RUB";//
    $item_params->total_price = "7850";//++
    $item_params->length = "1";
    $item_params->width = "1";
    $item_params->height = "1";
    $package_params->item_params[] = $item_params;//
    $item_params = new OpenItemParam;
    $item_params->item_id = "4000322322571";//
    $item_params->quantity = "1";//
    $item_params->english_name = "Utyug Parogenerator Morphy Richards Saturn Intellitemp 305003 Utyug-Parogenerator";//
    $item_params->local_name = "4000322322571";//
    $item_params->unit_price = "7850";//++
    $item_params->sku = "5011832067364";//++
//    $item_params->sc_item_id = "1";
    $item_params->weight = "1";//
    $item_params->item_features = "cf_normal";//
    $item_params->currency = "RUB";//
    $item_params->total_price = "7850";//++
    $item_params->length = "1";
    $item_params->width = "1";
    $item_params->height = "1";
    $package_params->item_params[] = $item_params;//
//    $package_params->currency = "USD";
    $order_param->package_params = $package_params;//*/

    $orderAli = json_decode($findorderbyidRes, true);
    $zip = $orderAli['receipt_address']['zip'];
    $city = $orderAli['receipt_address']['city'];
    $province = $orderAli['receipt_address']['province'];
    $street = $orderAli['receipt_address']['detail_address'];
    $detail_address = $orderAli['receipt_address']['detail_address'];

    $c = new TopClient;
    $c->format = 'json';
    $c->appkey = APPKEY;
    $c->secretKey = SECRET;
    $req = new CainiaoGlobalLogisticOrderCreateRequest;
    $order_param = new OpenOrderParam;//
    $trade_order_param = new OpenTradeOrderParam;//
    $trade_order_param->trade_order_id = $order['name']; // order name
    $order_param->trade_order_param = $trade_order_param;// order name

    $solution_param = new OpenSolutionParam;//
    $solution_param->solution_code = "$logistics_type"; // $logistics_type

    $service_params = new OpenServiceParam;//
    $service_params->code = "DOOR_PICKUP";//++


    $features = new Features;
    $features->warehouse_code = "$warehouse_code";//
//    $features->service_resource_code="CAINIAO_STANDARD_30228135";
//    $features->express_mail_no="LBxxxx001";
//    $features->express_company_id="100";
//    $features->express_company_name="顺丰快递";
    $service_params->features = $features;//
    $solution_param->service_params = $service_params;
    $order_param->solution_param = $solution_param;//


    $seller_info_param = new OpenSellerInfoParam;
    $order_param->seller_info_param = $seller_info_param;

    $sender_param = new OpenSenderParam;//
    $sender_param->seller_address_id = "$sender";//
    $order_param->sender_param = $sender_param;//

    $returner_param = new OpenReturnerParam;//
    $returner_param->seller_address_id = "$refund";//
    $order_param->returner_param = $returner_param;//

    $receiver_param = new ReceiverParam;//
    $receiver_param->name = $order['_attributes']['ФИО'];//++
    $receiver_param->telephone = $order['_attributes']['Телефон'];//++
    $address_param = new OpenAddressParam;
    $address_param->zip_code = $zip;// ++
    $address_param->country_name = "RU";//++
    $address_param->province = $province;//++
    $address_param->city = $city;//++
    $address_param->district = "";//++
    $address_param->street = $street;//++
    $address_param->detail_address = $detail_address;//++
    $address_param->country_code = "RU";//++
    $receiver_param->address_param = $address_param;//
    $order_param->receiver_param = $receiver_param;//

    $pickup_info_param = new OpenPickupInfoParam;//
    $pickup_info_param->seller_address_id = "$pickup";//
    $order_param->pickup_info_param = $pickup_info_param;//

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
        $item_params->local_name = $orderAliproduct['product_id'];//
        $item_params->unit_price = $price;//++
        $item_params->sku = $orderAliproduct['sku_code'] ?? '';//++
        $item_params->weight = "1";//
        $item_params->item_features = "cf_normal";//
        $item_params->currency = "RUB";//
        $item_params->total_price = $item_params->unit_price * $item_params->quantity;//++
        $item_params->length = "1";
        $item_params->width = "1";
        $item_params->height = "1";
        $package_params->item_params[] = $item_params;//
    }
    $order_param->package_params = $package_params;//
    $req->setOrderParam(json_encode($order_param));//
    $req->setLocale("ru_RU"); //
    $resp = $c->execute($req, $sessionKey);

    return $resp;
}

function firstMile($address_id, $solution_code, $sessionKey)
{

    $c = new TopClient;
    $c->appkey = APPKEY;
    $c->secretKey = SECRET;
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

function createHandoverList($refund, $pickup, $order_code_list, $sessionKey)
{

    $c = new TopClient;
    $c->appkey = APPKEY;
    $c->secretKey = SECRET;
    $c->format = "json";

    $req = new CainiaoGlobalHandoverCommitRequest;
    $user_info = new UserInfoDto;
//    $user_info->top_user_key = "123";
    $req->setUserInfo(json_encode($user_info));
//    $req->setRemark("易碎");
    $return_info = new ReturnerDto;
//    $address = new AddressDto;
//    $address->zip_code = "310012";
//    $address->detail_address = "文一西路680菜鸟";
//    $address->street = "蒋村街道";
//    $address->district = "西湖区";
//    $address->city = "杭州市";
//    $address->province = "浙江省";
//    $address->country = "中国";
//    $return_info->address = $address;
//    $return_info->email = "alibaba@alibaba-inc.com";
//    $return_info->mobile = "1760x000007";
//    $return_info->phone = "098-234234";
//    $return_info->name = "张三";
    $return_info->address_id = "$refund";
    $req->setReturnInfo(json_encode($return_info));
    $pickup_info = new PickupDto;
    $address = new AddressDto;
//    $address->zip_code = "123456";
//    $address->detail_address = "文一西路680菜鸟";
//    $address->street = "蒋村街道";
//    $address->district = "西湖区";
//    $address->city = "杭州市";
//    $address->province = "浙江省";
//    $address->country = "中国";
//    $pickup_info->address = $address;
//    $pickup_info->email = "alibaba@alibaba-inc.com";
//    $pickup_info->mobile = "1760x000007";
//    $pickup_info->phone = "098-234234";
//    $pickup_info->name = "张三";
    $pickup_info->address_id = "$pickup";
    $req->setPickupInfo(json_encode($pickup_info));
//    $req->setWeight("1000");
//    $req->setHandoverOrderId("100001");
//    $req->setWeightUnit("kg");
//    $req->setType("self_post");
    $req->setClient("ISV-aliekspress_kompaniya");

    $req->setOrderCodeList($order_code_list);

    $req->setLocale("ru_RU"); //
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
//    $user_info->top_user_key="123";
    $req->setUserInfo(json_encode($user_info));
    $req->setClient("ISV-aliekspress_kompaniya");
    $req->setHandoverContentId("$handoverContentId");
    $req->setType("512");
    $req->setLocale("ru_RU"); //
    $resp = $c->execute($req, $sessionKey);

    return $resp;
}


/*1. Получение списка адресов продавца, сохранённых в системе ++*/
$getLogisticAddressesRes = getLogisticAddresses($sessionKey);
$sender = $getLogisticAddressesRes->sender_seller_address_list->senderselleraddresslist[0]->address_id ?? -1;
$pickup = $getLogisticAddressesRes->pickup_seller_address_list->pickupselleraddresslist[0]->address_id ?? -1;
$refund = $getLogisticAddressesRes->refund_seller_address_list->refundselleraddresslist[0]->address_id ?? -1;

/*1.1. Получение деталей заказа из АЕ ++*/
$findorderbyidRes = findorderbyid($order, $sessionKey);
$logistics_type = 'AE_RU_MP_COURIER_PH3';
switch ($findorderbyidRes) {
    case strpos($findorderbyidRes, 'AE_RU_MP_PUDO_PH3') > 0:
        $logistics_type = 'AE_RU_MP_PUDO_PH3';
        break;
    case strpos($findorderbyidRes, 'AE_RU_MP_OVERSIZE_PH3') > 0:
        $logistics_type = 'AE_RU_MP_OVERSIZE_PH3';
        break;
    case strpos($findorderbyidRes, 'AE_RU_MP_RUPOST_PH3_FR_FR') > 0:
        $logistics_type = 'AE_RU_MP_RUPOST_PH3_FR_FR';
        break;
}

/*2. Получение списка логистических решений, доступных для отгрузки заказа ++*/
if ($logistics_type != 'AE_RU_MP_COURIER_PH3') {
    $cainiaoGlobalSolutionInquiryRes = cainiaoGlobalSolutionInquiry($order, $sessionKey);
    if (strpos(json_encode($cainiaoGlobalSolutionInquiryRes), $logistics_type) > 0) {
        echo "Logistic $logistics_type solution allowed";
    } else {
        echo "ERRR!!! Logistic $logistics_type solution not allowed. Set AE_RU_MP_COURIER_PH3 instead.";
        $logistics_type = 'AE_RU_MP_COURIER_PH3';
    }
} else {
    echo "Logistic AE_RU_MP_COURIER_PH3 solution allowed";
}


/*3. Получение списка кодов ресурсов, доступных для адреса отгрузки
 и выбранного режима первой мили ++*/

$firstMileRes = firstMile($sender, $logistics_type, $sessionKey);
$warehouse_code = $firstMileRes->result->result->solution_service_res_list->solution_service_res_dto[0]->code;

/*4. Создание логистического заказа ++ */

$cainiaoGlobalLogisticOrderCreate = cainiaoGlobalLogisticOrderCreate($findorderbyidRes, $sender, $pickup, $refund, $logistics_type, $warehouse_code, $order, $sessionKey);
if (!isset($cainiaoGlobalLogisticOrderCreate->result->logistics_order_id)) {
    var_dump('logistics_order_id undefined');
    var_dump($cainiaoGlobalLogisticOrderCreate);
    die();
}
$logistics_order_id = $cainiaoGlobalLogisticOrderCreate->result->logistics_order_id;
sleep(2);
var_dump($logistics_order_id);
var_dump($cainiaoGlobalLogisticOrderCreate);
die();

/*ДОБАВИТЬ ТРЕК И LP В МС*/

/*5. Скачивание данных логистического заказа ++*/
$getLogisticOrderInfoRes = getLogisticOrderInfo($order, $logistics_order_id, $sessionKey);
if (isset($getLogisticOrderInfoRes->result_list->result[0])) {
    $lp_number = $getLogisticOrderInfoRes->result_list->result[0]->lp_number;
    $internationallogistics_id = $getLogisticOrderInfoRes->result_list->result[0]->internationallogistics_id;
}
var_dump($lp_number, $internationallogistics_id);


/*ОТГРУЗИТЬ В МС*/

/*6. Получение PDF для печати этикетки заказа ++*/
$printInfoResp = printInfo($internationallogistics_id, $sessionKey);
echo 'наклейка' . PHP_EOL;
$b64 = json_decode($printInfoResp->result)->body;

$bin = base64_decode($b64, true);

file_put_contents("наклейка_$order.pdf", $bin);


/*NEXT STAGE 2 - 9AM CREATE HANDOVER AND SHARE TELEGRAM*/

/*7. Создание листа передачи (контейнера/паллеты) и получение его id ++*/

//$getLogisticAddressesRes = getLogisticAddresses($sessionKey);
//$sender = $getLogisticAddressesRes->sender_seller_address_list->senderselleraddresslist[0]->address_id ?? -1;
//$pickup = $getLogisticAddressesRes->pickup_seller_address_list->pickupselleraddresslist[0]->address_id ?? -1;
//$refund = $getLogisticAddressesRes->refund_seller_address_list->refundselleraddresslist[0]->address_id ?? -1;
//
//$order_code_list = "LP00406261257984,LP00406237657756,LP00406304675921";
//$createHandoverListResp = createHandoverList($refund, $pickup, $order_code_list, $sessionKey);
//$handoverContentId = $createHandoverListResp->result->data->handover_content_id;

/*8. Получение этикетки контейнера (паллеты) и листа передачи ++*/

//$handoverContentId = 1485454;
//$printHandoverListResp = printHandoverList($sessionKey, $handoverContentId);
//echo 'лист передачи' . PHP_EOL;
////var_dump($printHandoverListResp);
//$b64 = json_decode($printHandoverListResp->result->data)->body;
//$bin = base64_decode($b64, true);
//file_put_contents("лист_передачи_$handoverContentId.pdf", $bin);


/*9. Отметка заказа как отгруженного в системе AliExpress*/
$logistics_type = "AE_RU_MP_RUPOST_PH3_FR_FR";

if ($logistics_type == "AE_RU_MP_COURIER_PH3") {
    $logistics_type = "AE_RU_MP_COURIER_PH3_CITY";
}
$order = "5005281069484525";
$internationallogistics_id = "14089351459210";
$sellerShipmentForTopResp = logisticsSellershipmentfortop($logistics_type, $order, $internationallogistics_id, $sessionKey);
var_dump($sellerShipmentForTopResp);
