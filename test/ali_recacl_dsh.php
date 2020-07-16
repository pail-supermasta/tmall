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
            $sessionKey = '50002501201pu1cabc58aBbzgXFkqhvplWEdrhpszGJkLnXgVzGGx9wxCcCqsvPj6As';
            break;
        case  stripos($attributes, "BESTGOODS (ID 5041091)") !== false :
            $sessionKey = "50002300a27NiS9iAoWsSgg7oyhIqbUREbiAT3L1a0922e9szEfgKpYHRon7CwFb0op";
            break;
        case  stripos($attributes, "Noerden (ID 5012047)") !== false :
            $sessionKey = "50002700f09CsXpqaf1l0159ee5e1ipRaztFcFeR5MtYHGEvJ1IQXHD3CpkVzlo6zzy";
            break;
        case  stripos($attributes, "Morphy Richards (ID 5017058)") !== false :
            $sessionKey = "50002500427yXPcbqwfU6Lmo2txf83nTdKufWRl134c668aABgs6PSvcIUUwtbSdEo3";
            break;
        case  stripos($attributes, "iRobot (ID 5016030)") !== false :
            $sessionKey = "50002500614qeCsLetipHO1psD19f649cde2oyGnyayqDXEjt6Kw2FnExerDL8xslku";
            break;
    }
    return $sessionKey;
}

function getMonth()
{


    $collection = (new Client('mongodb://MSSync-read:1547e70be122dc285a2d24ad@23.105.225.41:27017/?authSource=MSSync&readPreference=primary&appname=MongoDB%20Compass&ssl=false'))->MSSync->customerorder;

    $cursor = $collection->find([
        'name' => '3004746280722679',
        /*'moment' => [
            '$gte' => '2020-07-01',
            '$lte' => '2020-07-15'
        ],*/
        '_agent' => '1b33fbc1-5539-11e9-9ff4-315000060bc8'
    ]);

    $orders = array();
    foreach ($cursor as $order) {
        $orders[$order['id']] = array('moment' => $order['moment'],
            'positions' => $order['_positions'],
            'description' => $order['description'],
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
    $sessionKey = getOrderShop($orderMS['description']);
    $findorderbyidRes = findorderbyid($orderMS['name'], $sessionKey);

    $dshSum = calcDSH(json_decode($findorderbyidRes, true));
    die();
}
// calc comission
function calcDSH($findorderbyidRes)
{
    /*ДШ amount*/
    $coupon = 0;
    $escrowFeeSum = 0;
    $positionsEscrowFee = array();



    $products = $findorderbyidRes['child_order_list']['global_aeop_tp_child_order_dto'];

    if (is_array($products)) {

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
            $orderShip = $shortener['logistics_amount']['cent'] ?? 0;

            $coupon = $productsTotal + $orderShip - $orderAmount;
            /*  gather escrow fee for each product id   */
            if (isset($product['escrow_fee_rate'])) {
                $positionEscrowFee = array($product['escrow_fee_rate']);
                array_push($positionsEscrowFee, $positionEscrowFee);
            }
            $escrowFeeSum += $product['escrow_fee_rate'];

        }
    }


    $couponEscrow = $coupon * $escrowFeeSum / sizeof($products);
    $escrowFee = $escrowFeeSum / sizeof($products) * $productsTotal;
    $orderShipEscrow = isset($findorderbyidRes['logisitcs_escrow_fee_rate']) ? $findorderbyidRes['logisitcs_escrow_fee_rate'] * $orderShip : 0;
    $orderDetails['dshSum'] = ($escrowFee + $orderShipEscrow - $couponEscrow) / 100;

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
    echo "$affiliateFeeSum / sizeof($productsAli)".PHP_EOL;
    echo "($pay_amount_by_settlement_cur - $logistics_amount / 100) * $affiliateFee".PHP_EOL;
    $affiliateAmount = ($pay_amount_by_settlement_cur - $logistics_amount / 100) * $affiliateFee;

    $dshSum = $orderDetails['dshSum'] + $affiliateAmount;
    return $dshSum;
}

// check if no 200 logistic product in MS
// add producd add comission

