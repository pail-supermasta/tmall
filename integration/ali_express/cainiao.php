<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 30.08.2019
 * Time: 16:26
 */


ini_set('display_errors', 1);

header('Content-Type: application/json');


require_once '../vendor/autoload.php';
require_once 'taobao/TopSdk.php';

//require_once '../moi_sklad/ms_get_orders_dynamic.php';


use Avaks\Cainiao\Cainiao;

// Get order details from Ali by ID
require_once 'ali_order_details_dynamic.php';

//$buyer_login_id = 'NezabudkaMR@yandex.ru';
//$sessionKey = '50002500500V1acb5e0esBdqLFskhnQwtwheYk1CiSexTFfFAv6nWUefGArBboUuh8F';


function deliverCainiao($order, $sessionKey)
{
    $curlCai = new Cainiao();

    /*get order details from MS*/

    $orderDetails = findorderbyid($order, $sessionKey);


    $zip = $orderDetails['receipt_address']['zip'];
    $address = $orderDetails['receipt_address']['address2'];
    $province = $orderDetails['receipt_address']['province'];
    $city = $orderDetails['receipt_address']['city'];
    $street = $orderDetails['receipt_address']['detail_address'];
    $name = $orderDetails['receipt_address']['contact_person'];
    $mobile = '7' . $orderDetails['receipt_address']['mobile_no'];


    $products = $orderDetails['child_order_list']['global_aeop_tp_child_order_dto'];

    $c = new TopClient;
    $aliexpressSolutionProductInfoGetRequest = new AliexpressSolutionProductInfoGetRequest;
    /*0 lvl list of products*/
    $packageList = array();
    /*1 lvl product size*/
    $packageDTO = array();
    /*2 lvl - product*/
    $goodsList = array();

    foreach ((array)$products as $product) {

        $c->appkey = APPKEY;
        $c->secretKey = SECRET;

        $aliexpressSolutionProductInfoGetRequest->setProductId($product['product_id']);
        $resp = $c->execute($aliexpressSolutionProductInfoGetRequest, $sessionKey);
        $res = json_encode((array)$resp);
        $shortener = json_decode($res, true);


        $length = $shortener['result']['package_length'];
        $width = $shortener['result']['package_width'];
        $height = $shortener['result']['package_height'];


        $itemId = $shortener['result']['product_id'];
        $goodsNameCn = $shortener['result']['product_unit'];
        $quantity = $product['product_count'] ?? 1;
        $goodsNameEn = $shortener['result']['subject'];
        $price = (int)$shortener['result']['product_price'];
        $weight = (int)$shortener['result']['gross_weight'];

        $goodsList[0] = array('isContainsBattery' => false,
            'itemId' => $itemId,
            'goodsNameCn' => $goodsNameCn,
            'quantity' => $quantity,
            'hscode' => null,
            'goodsNameEn' => $goodsNameEn,
            'price' => $price,
            'weight' => $weight,
            'isAneroidMarkup' => false);

        $packageList[] = array('length' => $length,
            'width' => $width,
            'height' => $height,
            'goodsList' => $goodsList
        );

    }



    $sourceArray = array(
        'DistributionConsignRequest' =>
            array(
                'orderSource' =>
                    array(
                        'tradeOrderId' => $order,
                        'tradeOrderFrom' => 'AE',
                    ),
                'receiver' =>
                    array(
                        'zip' => $zip,
                        'address' => $address.$street,
                        'province' => $province,
                        'city' => $city,
                        'countryCode' => 'RU',
                        'street' => $street,
                        'name' => $name,
                        'mobile' => $mobile,
                        'telephone' => null,
                    ),
                'sender' =>
                    array(
                        'zip' => '117246',
                        'address' => 'Россия Москва Москва Херсонская ул, дом 43, корпус 3',
                        'province' => 'Москва',
                        'city' => 'Москва',
                        'countryCode' => 'RU',
                        'street' => 'Херсонская',
                        'name' => 'ООО Незабудка',
                        'mobile' => '79255242747',
                        'county' => 'Россия',
                        'telephone' => '79255242747',
                        'addressId' => -1,
                    ),
                'packageList' => $packageList,

                'consignContract' =>
                    array(
                        'refundAddress' =>
                            array(
                                'zip' => '117246',
                                'address' => 'Россия Москва Москва Херсонская ул, дом 43, корпус 3',
                                'province' => 'Москва',
                                'city' => 'Москва',
                                'countryCode' => 'RU',
                                'street' => 'Херсонская',
                                'name' => 'ООО Незабудка',
                                'mobile' => '79255242747',
                                'county' => 'Россия',
                                'telephone' => '79255242747',
                                'addressId' => -1,
                            ),
                        'undeliverableOption' => false,
                        'pickupAddress' =>
                            array(
                                'zip' => '117246',
                                'address' => 'Россия Москва Москва Херсонская ул, дом 43, корпус 3',
                                'province' => 'Москва',
                                'city' => 'Москва',
                                'countryCode' => 'RU',
                                'street' => 'Херсонская',
                                'name' => 'ООО Незабудка',
                                'mobile' => '79255242747',
                                'county' => 'Россия',
                                'telephone' => '79255242747',
                                'addressId' => -1,
                            ),
                        'expressCompany' => null,
                        'warehouseCarrierService' => 'AE_RU_MP_COURIER_PH3_13329064',
                        'needPickup' => true,
                    ),
            ),
    );



    $content = json_encode($sourceArray, JSON_UNESCAPED_UNICODE);


    /*CAINIAO_GLOBAL_OPEN_DISTRIBUTION_CONSIGN*/

    $resp = $curlCai->CAINIAO_GLOBAL_OPEN_DISTRIBUTION_CONSIGN($content);
    $message = "Заказ №$order - создан в Цайняо метод CAINIAO_GLOBAL_OPEN_DISTRIBUTION_CONSIGN";
    telegram($message, '-278688533');


    /*{"DistributionConsignResponse":{"logisticsOrderId":"147002440549","tradeLogisticsOrderId":"5137660691","tradeOrderId":"5000269028796387","tradeOrderFrom":"AE","logisticsOrderCode":"LP00147002440549"},"success":"true"}*/

    $distributionConsignResponse = json_decode($resp, true)['DistributionConsignResponse'];
    /*get LP*/

    $sourceArray = array(
        'orderId' => $distributionConsignResponse['logisticsOrderId'], //Digits that follow the LP# (received in the response to DISTRIBUTION_CONSIGN)
        'cnId' => '4398983084403' //Cainiao user ID of the store
    );

    $content = json_encode($sourceArray, JSON_UNESCAPED_UNICODE);

    /*MAILNO_QUERY_SERVICE*/
    $res = $curlCai->MAILNO_QUERY_SERVICE($content);


    /*EXAMPLE RESPONSE string(47) "{"mailNo":"AEWH0000708100RU4","success":"true"}"*/

    $mailNoResponse = json_decode($res, true);
    $mailNo = $mailNoResponse['mailNo'];
    var_dump($mailNoResponse);

    $message = "Заказ №$order - получен трек номер $mailNo из Цайняо метод MAILNO_QUERY_SERVICE. Заказ не будет доставлен";
    telegram($message, '-278688533');
    $message = "ВНИМАНИЕ Обработать заказ $order ВРУЧНУЮ";
    telegram($message, '-278688533');


    /*AliexpressLogisticsGetpdfsbycloudprintRequest*/

    /*add pdf to order MS*/

    /*AliexpressLogisticsSellershipmentfortopRequest*/
//    return array('mailNo' => $mailNo, 'result_success' => true);

    return sellerShipmentForTop($order, $mailNo, $sessionKey);
}

function sellerShipmentForTop($order, $logisticsNo, $sessionKey)
{
    $c = new TopClient;
    $c->format = 'json';
    $c->appkey = APPKEY;
    $c->secretKey = SECRET;
    $req = new AliexpressLogisticsSellershipmentfortopRequest;
    $req->setLogisticsNo("$logisticsNo");
//$req->setDescription("memo");
    $req->setSendType("all");
    $req->setOutRef("$order");
    $req->setTrackingWebsite("https://global.cainiao.com/");
    $req->setServiceName("AE_RU_MP_COURIER_PH3_CITY");
    $resp = $c->execute($req, $sessionKey);
    $result_success = $resp->result_success ?? false;


    /*EXAMPLE RESPONSE object(stdClass)#4 (2) {
      ["result_success"]=>
      bool(true)
      ["request_id"]=>
      string(12) "obnowjcs3yko"
    }*/

    return array('mailNo' => $logisticsNo, 'result_success' => $result_success);
}


/*define('APPKEY', '27862248');
define('SECRET', 'ca6916e55a087b3561b5077fc8b83ee6');
deliverCainiao('5000281306086387', $sessionKey);*/