<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 30.08.2019
 * Time: 16:26
 */


ini_set('display_errors', 1);

ini_set("error_log", "../php-error.log");
header('Content-Type: application/json');


require_once '../vendor/autoload.php';
require_once 'taobao/TopSdk.php';


use Avaks\Cainiao\Cainiao;

// Get order details from Ali by ID
require_once 'ali_order_details_dynamic.php';


function deliverCainiao($order, $cnId, $cpCode, $sessionKey)
{


    /*save LP to the MS*/
    $orderMS = new \Avaks\MS\OrderMS('', $order, '');
    $orderMSDetails = $orderMS->getByName();
    $orderMS->id = $orderMSDetails['id'];


    /*check if no LP*/
    $curlCai = new Cainiao($cpCode);
    if (!ctype_digit($orderMSDetails['externalCode'])) {


        /*get order details from Ali*/

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
            $goodsNameEn = preg_replace("/[^A-Za-z0-9. ]/", '', $shortener['result']['subject']);
            if (isset($shortener['result']['product_price'])) {
                $price = (int)$shortener['result']['product_price'];
            } elseif (isset($shortener['result']['aeop_ae_product_s_k_us']['global_aeop_ae_product_sku'])) {
                foreach ($shortener['result']['aeop_ae_product_s_k_us']['global_aeop_ae_product_sku'] as $sku) {
                    if ($product['sku_code'] == $sku['sku_code']) {
                        $price = isset($sku['sku_discount_price']) ? (int)$sku['sku_discount_price'] : (int)$sku['sku_price'];
                    }
                }
            }

            $weight = (int)$shortener['result']['gross_weight'] == 0 ? 1 : (int)$shortener['result']['gross_weight'];


//        $weight = $shortener['result']['gross_weight'];

            $goodsList[] = array(
                'isContainsBattery' => false,
                'itemId' => $itemId,
                'goodsNameCn' => $goodsNameCn,
                'quantity' => $quantity,
                'hscode' => null,
                'goodsNameEn' => $goodsNameEn,
                'price' => $price,
                'weight' => $weight,
                'isAneroidMarkup' => false);
        }
        $packageList[0] = array(
            'length' => 1,
            'width' => 1,
            'height' => 1,
            'goodsList' => $goodsList
        );


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
                            'address' => $address . $street,
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
                            'zip' => '115114',
                            'address' => 'Россия, Москва, Павелецкая ул, дом 2, стр 21, оф 237',
                            'province' => 'Москва',
                            'city' => 'Москва',
                            'countryCode' => 'RU',
                            'street' => 'Павелецкая',
                            'name' => 'ООО Незабудка',
                            'mobile' => '74954812282',
                            'county' => 'Россия',
                            'telephone' => '74954812282 доб 121',
                            'addressId' => -1,
                        ),
                    'packageList' => $packageList,

                    'consignContract' =>
                        array(
                            'refundAddress' =>
                                array(
                                    'zip' => '115114',
                                    'address' => 'Россия, Москва, Павелецкая ул, дом 2, стр 21, оф 237',
                                    'province' => 'Москва',
                                    'city' => 'Москва',
                                    'countryCode' => 'RU',
                                    'street' => 'Павелецкая',
                                    'name' => 'ООО Незабудка',
                                    'mobile' => '74954812282',
                                    'county' => 'Россия',
                                    'telephone' => '74954812282 доб 121',
                                    'addressId' => -1,
                                ),
                            'undeliverableOption' => false,
                            'pickupAddress' =>
                                array(
                                    'zip' => '115114',
                                    'address' => 'Россия, Москва, Павелецкая ул, дом 2, стр 21, оф 237',
                                    'province' => 'Москва',
                                    'city' => 'Москва',
                                    'countryCode' => 'RU',
                                    'street' => 'Павелецкая',
                                    'name' => 'ООО Незабудка',
                                    'mobile' => '74954812282',
                                    'county' => 'Россия',
                                    'telephone' => '74954812282 доб 121',
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
        var_dump($resp);
        error_log(date("Y-m-d H:i:s", strtotime(gmdate("Y-m-d H:i:s")) + 3 * 60 * 60) . json_encode($resp) . $content . PHP_EOL, 3, 'CAINIAO_GLOBAL_OPEN_DISTRIBUTION_CONSIGN.log');


        /*{"DistributionConsignResponse":{"logisticsOrderId":"147002440549","tradeLogisticsOrderId":"5137660691","tradeOrderId":"5000269028796387","tradeOrderFrom":"AE","logisticsOrderCode":"LP00147002440549"},"success":"true"}*/

        $distributionConsignResponse = json_decode($resp, true)['DistributionConsignResponse'];
        /*get LP*/

        if (!isset($distributionConsignResponse['logisticsOrderId']) && $distributionConsignResponse['logisticsOrderId'] == '') {
            $message = "ОШИБКА СОЗДАНИЯ В CAINIAO заказа $order. Метод CAINIAO_GLOBAL_OPEN_DISTRIBUTION_CONSIGN.";
            telegram($message, '-320614744');
//            var_dump($orderMS->setStateProcessManually());
        } else {
            var_dump($orderMS->setLP($distributionConsignResponse['logisticsOrderId']));
        }


    } else {
        $distributionConsignResponse['logisticsOrderId'] = $orderMSDetails['externalCode'];
    }


    $sourceArray = array(
        'orderId' => $distributionConsignResponse['logisticsOrderId'], //Digits that follow the LP# (received in the response to DISTRIBUTION_CONSIGN)
        'cnId' => $cnId //Cainiao user ID of the store
    );

    $content = json_encode($sourceArray, JSON_UNESCAPED_UNICODE);

    /*MAILNO_QUERY_SERVICE*/
    $res = $curlCai->MAILNO_QUERY_SERVICE($content);

    error_log(date("Y-m-d H:i:s", strtotime(gmdate("Y-m-d H:i:s")) + 3 * 60 * 60) . json_encode($res) . $content . PHP_EOL, 3, 'MAILNO_QUERY_SERVICE.log');

    /*EXAMPLE RESPONSE string(47) "{"mailNo":"AEWH0000708100RU4","success":"true"}"*/

    $mailNoResponse = json_decode($res, true);

    var_dump($mailNoResponse);
    if (isset($mailNoResponse['mailNo']) && $mailNoResponse['mailNo'] != '') {
        $mailNo = $mailNoResponse['mailNo'];
        $message = "Заказ №$order - получен трек номер $mailNo из Цайняо метод MAILNO_QUERY_SERVICE.";
        telegram($message, '-320614744');
    } else {
        $message = "ОШИБКА ПОЛУЧЕНИЯ ТРЕКА в заказе $order. Метод MAILNO_QUERY_SERVICE.";
        telegram($message, '-320614744');
//        var_dump($orderMS->setStateProcessManually());
    }


    /*AliexpressLogisticsGetpdfsbycloudprintRequest*/

    /*add pdf to order MS*/

    /*AliexpressLogisticsSellershipmentfortopRequest*/
    return array('mailNo' => $mailNo, 'result_success' => true);

//    return sellerShipmentForTop($order, $mailNo, $sessionKey);
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