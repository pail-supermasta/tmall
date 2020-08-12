<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
date_default_timezone_set('Europe/Moscow');


require_once "../integration/vendor/autoload.php";
require_once '../integration/ali_express/taobao/TopSdk.php';

use MongoDB\Client;


define('APPKEY', '27862248');
define('SECRET', 'ca6916e55a087b3561b5077fc8b83ee6');


function getOrderShop($attributes)
{
    $sessionKey = '';
    switch (true) {
        case stripos($attributes, "novinkiooo (ID 4901001)") !== false :
            $sessionKey = '50002500501Vs15192a4fBdqLHT9iHrwvpCb0JydnTDyqj8eFwAOsukoFc4qg0Vxn2C';
            break;
        case  stripos($attributes, "BESTGOODS (ID 5041091)") !== false :
            $sessionKey = "50002301005q0OsaZ16afcaa0zGzFoxi0th7ccSgMw0CJduh1FqvAlZmPkEo4jGYCQw";
            break;
        case  stripos($attributes, "Noerden (ID 5012047)") !== false :
            $sessionKey = "50002701a33kuBupddegw9sTukkGPKzFsUBHbjPQDPTVU16922027tN2z5kWwXYAwTx";
            break;
        case  stripos($attributes, "Morphy Richards (ID 5017058)") !== false :
            $sessionKey = "50002500435yXPcbqwfU6Lmo2txf83nTdKufWRlABgs6PSv10e2657ccIUUwtbSdEo3";
            break;
        case  stripos($attributes, "iRobot (ID 5016030)") !== false :
            $sessionKey = "50002500111c0AyTocCxSEw142e6ac49kntzvuDe7g0eIqC0sf6gjSBLVrYF8ZUBSSy";
            break;
    }
    return $sessionKey;
}

function getMonth()
{


    $collection = (new Client('mongodb://MSSync-read:1547e70be122dc285a2d24ad@23.105.225.41:27017/?authSource=MSSync&readPreference=primary&appname=MongoDB%20Compass&ssl=false'))->MSSync->customerorder;

    $cursor = $collection->find([
//        'name'=>'8016972740385301',
        'moment' => [
            '$gte' => '2020-07-15',
            '$lte' => '2020-07-18'
        ],
        '_state' => [
            '$nin' => array(
                '552a994e-2905-11e7-7a31-d0fd002c3df2',
                '327c0111-75c5-11e5-7a40-e89700139936',
                '327c070c-75c5-11e5-7a40-e8970013993b'
            )
        ],
        '_agent' => '1b33fbc1-5539-11e9-9ff4-315000060bc8'
    ]);

    $orders = array();
    foreach ($cursor as $order) {
        $orders[$order['id']] = array('moment' => $order['moment'],
            'positions' => $order['_positions'],
            'description' => $order['description'],
            'id' => $order['id'],
            'name' => $order['name']
        );
    }
    return $orders;
}


function findorderbyid($post_data, $sessionKey)
{

    $c = new TopClient;
    $c->format = "json";
    $c->appkey = APPKEY;
    $c->secretKey = SECRET;
    $req = new AliexpressSolutionOrderInfoGetRequest;
    $param1 = new OrderDetailQuery;
//$param1->ext_info_bit_flag="11111";
    $param1->order_id = $post_data;
    $req->setParam1(json_encode($param1));
    $resp = $c->execute($req, $sessionKey);

    if (!isset($resp->result)) {
        return false;
    } else {
        $result = $resp->result->data;
        $res = json_encode((array)$result);

//        return json_decode($res, true);
    }


    return $res;
}


// get orders from MS for 1 month
$ordersMS = getMonth();

// foreach get details from ali
foreach ($ordersMS as $orderMS) {
    $orderDetails['positions'] = '';
    $positions = [];



    $sessionKey = getOrderShop($orderMS['description']);
    $findorderbyidRes = findorderbyid($orderMS['name'], $sessionKey);
    $findorderbyidRes = json_decode($findorderbyidRes, true);

    $dshSum = calcDSH($findorderbyidRes);
    if (is_bool($dshSum)) continue;

    // check if no 200 logistic product in MS
    $logisticProductFound = false;
    foreach ($orderMS['positions'] as $position) {
        if ($position['id'] == '655b68a8-7695-11e5-90a2-8ecb002886e7') {
            $logisticProductFound = true;
        }
        $id = $position['id'];
        $oldPosition = array(
            "quantity" => $position['quantity'],
            "price" => $position['price'],
            "vat" => 0,
            "assortment" =>
                array(
                    "meta" =>
                        array(
                            "href" => "https://online.moysklad.ru/api/remap/1.1/entity/product/$id",
                            "type" => "product",
                            "mediaType" => "application/json"
                        ),
                ),
            "reserve" => $position['reserve']
        );
        array_push($positions, $oldPosition);

    }
    if (!$logisticProductFound &&
        isset($findorderbyidRes['logistics_amount']['cent']) &&
        $findorderbyidRes['logistics_amount']['cent'] > 0) {
        $logistic_product = array(
            "quantity" => 1,
            "price" => $findorderbyidRes['logistics_amount']['cent'],
            "vat" => 0,
            "assortment" =>
                array(
                    "meta" =>
                        array(
                            "href" => "https://online.moysklad.ru/api/remap/1.1/entity/service/655b68a8-7695-11e5-90a2-8ecb002886e7",
                            "type" => "service",
                            "mediaType" => "application/json"
                        ),
                ),
            "reserve" => 1
        );

        array_push($positions, $logistic_product);

    }
    $orderDetails['positions'] = json_encode($positions, JSON_UNESCAPED_SLASHES);

    if (!$logisticProductFound) {
        $postPositions = ',
                  "positions": ' . $orderDetails['positions'] . '';
    } else {
        $postPositions = '';
    }
    $postdata = '{
                  "attributes": [{
                        "id": "535dd809-1db1-11ea-0a80-04c00009d6bf",
                        "value": ' . $dshSum . '
                  }]' . $postPositions . '
                }';

    $orderMSInstance = new \Avaks\MS\OrderMS($orderMS['id']);

    $updateOrderResp = $orderMSInstance->updateOrder($postdata);
}
// calc comission
function calcDSH($findorderbyidRes)
{
//    var_dump($findorderbyidRes);
    /*ДШ amount*/
    $coupon = 0;
    $escrowFeeSum = 0;
    $positionsEscrowFee = array();


    $products = $findorderbyidRes['child_order_list']['global_aeop_tp_child_order_dto'];

    if (is_array($products)) {
        $productDSHS = 0;
        foreach ($products as $product) {
            $productsTotal = 0;
            $orderAmount = 0;

            foreach ($products as $item) {
                $prPrice = $item['product_price']['cent'];
                $prCo = $item['product_count'];
                $prTotal = $prPrice * $prCo;
                $productsTotal += $prTotal;
                $orderAmount = $findorderbyidRes['order_amount']['cent'];
            }
            // product dsh
            if (!isset($product['escrow_fee_rate'])) {
                var_dump($findorderbyidRes["id"]);
                return false;
            }
            $productDSH = $product['product_price']['cent'] * $product['escrow_fee_rate'] * $product['product_count'];

            $productDSHS += $productDSH;
            $orderShip = $findorderbyidRes['logistics_amount']['cent'] ?? 0;

            $coupon = $productsTotal + $orderShip - $orderAmount;
            /*  gather escrow fee for each product id   */
            if (isset($product['escrow_fee_rate'])) {
                $positionEscrowFee = array($product['escrow_fee_rate']);
                array_push($positionsEscrowFee, $positionEscrowFee);
            }
            $escrowFeeSum += $product['escrow_fee_rate'];

        }
    }

//    var_dump($positionsEscrowFee);

    $couponEscrow = $coupon * $escrowFeeSum / sizeof($products);
    $escrowFee = $escrowFeeSum / sizeof($products) * $productsTotal;
//    echo "$escrowFeeSum / sizeof($products) * $productsTotal";
//    var_dump($escrowFee);
    $orderShipEscrow = isset($findorderbyidRes['logisitcs_escrow_fee_rate']) ? $findorderbyidRes['logisitcs_escrow_fee_rate'] * $findorderbyidRes['logistics_amount']['cent'] : 0;
//    var_dump($findorderbyidRes['logisitcs_escrow_fee_rate'] * $findorderbyidRes['logistics_amount']['cent']);
//    echo "(($escrowFee + $orderShipEscrow - $couponEscrow) / 100)";
    $orderDetails['dshSum'] = ($productDSHS + $orderShipEscrow - $couponEscrow) / 100;

    /*Affiliate amount*/

    $pay_amount_by_settlement_cur = $findorderbyidRes['pay_amount_by_settlement_cur'];
    $logistics_amount = $findorderbyidRes['logistics_amount']['cent'] ?? 0;

    $productsAli = $findorderbyidRes['child_order_list']['global_aeop_tp_child_order_dto'];
    $affiliateFeeSum = 0;

    foreach ($productsAli as $productAli) {
        if (isset($productAli['afflicate_fee_rate'])) {
            $affiliateFeeSum += $productAli['afflicate_fee_rate'];
        }
    }

    $affiliateFee = $affiliateFeeSum / sizeof($productsAli);
    $affiliateAmount = ($pay_amount_by_settlement_cur - $logistics_amount / 100) * $affiliateFee;

    $dshSum = $orderDetails['dshSum'] + $affiliateAmount;
    return $dshSum;
}



