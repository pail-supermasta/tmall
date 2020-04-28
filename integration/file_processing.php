<?php

ini_set('display_errors', 1);

header('Content-Type: application/json');


/*  INCLUDES    */

// Error handlers
//include_once 'class/error.php';

// Get orders from MS by ID
require_once 'moi_sklad/ms_get_orders_dynamic.php';
// Get order details from Ali by ID
require_once 'ali_express/ali_order_details_dynamic.php';
// Create order in MS
require_once 'moi_sklad/ms_post_order_dynamic.php';
// Find product in MS DB by produci_id from ALI
require_once 'moi_sklad/ms_mysql_products_dynamic.php';
require_once 'vendor/autoload.php';

use Avaks\MS\MSSync;

/*TEST PART DELETE ATER*/

/*define('APPKEY', '27862248');
define('SECRET', 'ca6916e55a087b3561b5077fc8b83ee6');
require_once 'ali_express/taobao/TopSdk.php';
// Telegram err logs integration
require_once 'class/telegram.php';



define('LOGINS', array(
    array(
        'name' => 'Незабудка MR',
        'login' => 'NezabudkaMR@yandex.ru',
        'field_id' => '0bbcd991-81f4-11e9-9109-f8fc0004dec9',
        'sessionKey' => '50002500428yXPcbqwfU6Lmo2txf83nTdKufWRlA15757c75Bgs6PSvcIUUwtbSdEo3',
        'cpCode' => 'UTV0a1NLakt5dE9DdzZOdEt1elhnblRnMURQaExvS0w4RVZEVHMyM2o2eTRqUjdiOEdxalpTVjhRN0ZBQldVZA==',
        'cnId' => '4398983084403'
    ),
    array(
        'name' => 'bestgoodsstore',
        'login' => 'bestgoodsstore@yandex.ru',
        'field_id' => '0bbcd3e6-81f4-11e9-9109-f8fc0004dec8',
        'sessionKey' => '50002300d289mlqwnqAebm1lqueSpe9ECuAKVtDg1b6f0174jvgyezVnDsmJ4OkbLKm',
        'cpCode' => 'QXJCQk1QcjJKTkZDbHk4ZVZ4bW11cFQ2L2QreW1XT0lJd2ZlMnEvL2dFZC9NbG5CSklEV2tiY0cxNkRSMWlYcQ==',
        'cnId' => '4398985192396'

    ),
    array(
        'name' => 'Новинки',
        'login' => 'novinkiooo@yandex.ru',
        'field_id' => 'e8a40577-77b9-11e9-912f-f3d40003d45d',
        'sessionKey' => '50003501012q0OsaZcIwdpta182c9ed30oFZdEu4qVXjhkvjakxWAd4HQ7yEr5SZx1z',
        'cpCode' => 'OTQwTzB2T1U3N1Nza0Y3OVRKMHZyVWtPL0RFRjJHczBqUHBDRHBqK05LVXdBc1pJRkk0THo1YUVLR21PNE5IZQ==',
        'cnId' => '4398985964371'
    ),
    array(
        'name' => 'Незабудка iRobot',
        'login' => 'NezabudkaiRobot@yandex.ru',
        'field_id' => '0bbcde02-81f4-11e9-9109-f8fc0004deca',
        'sessionKey' => '50002500403yXPc1faa68e8bqNFyEIFqyQvdh3JzliwbSpFffdP8tU1DJbFGQte8yE2',
        'cpCode' => 'czJBM3dFNm9aQ0RuSnhnY0tEK2p2a3g1cEI5aFYwSGw1TlpxTVAyUE1CYk1iYkRCTU1tWENocFo4alU3aFdmUg==',
        'cnId' => '4398985334183'
    ),
    array(
        'name' => 'Незабудка ND',
        'login' => 'NezabudkaND@yandex.ru',
        'field_id' => '0bbce15c-81f4-11e9-9109-f8fc0004decb',
        'sessionKey' => '50002700524VsBdqLktCoFvXnxeB4JxEjRbW111b877fTD7DDviKW1DmhKcqJnlQq9B',
        'cpCode' => 'V1ZDUlZnY09vbHoyQTFpNEZEUElkcGlmUE43Z1hYZEdoVEZwM2huTDlWeWVKUHdIUmY4QmFWV1FOdXVCT3JQeg==',
        'cnId' => '4398983195649'
    )
));
//$order = '5000620116289901'; //0
//$order = '5000481033994782'; //2
//$order = '8003767178094779'; //1
$order = '5000712847875450'; //BG
//$order = '5001720021532591'; //mr


//$order = '5000550696796183';
$sessionKey = '50002300d289mlqwnqAebm1lqueSpe9ECuAKVtDg1b6f0174jvgyezVnDsmJ4OkbLKm';
//$sessionKey = '50002500428yXPcbqwfU6Lmo2txf83nTdKufWRlA15757c75Bgs6PSvcIUUwtbSdEo3';
//$sessionKey = '50002700524VsBdqLktCoFvXnxeB4JxEjRbW111b877fTD7DDviKW1DmhKcqJnlQq9B';
$login = 'bestgoodsstore@yandex.ru';
//$login = 'NezabudkaMR@yandex.ru';
//$login = 'NezabudkaND@yandex.ru';

$res = findorderbyid($order, $sessionKey);

destructResponse($res, $order, $login);

die();*/

/*TEST PART DELETE ATER*/


/*  Data validation according https://docs.google.com/document/d/1jzyjiFzT44BoxQCnYYeg2TmOz40yIe4C/edit */

/*  AF-3
 1) отсутствует 3 обязательных ФИО
 2) 2.1 индекс не соответствует формату -  6 цифр где первые три цифры между 100 и 999
    2.2 не указано одно из - Index, City, Street
 3) 3.1 код страны не содержит 7
    3.2 телефон не содержит 7 цифр - формат 999 99 99
*/


function getProductByIDMongo($code)
{
    $filter = ['code' => $code];

    $collection = (new MSSync())->MSSync;
    $product = $collection->product->findOne($filter);
    if (isset($product['_id'])) {
        $item['id'] = $product['_id'];
        $item['minPrice'] = $product['minPrice']['value'];
        $item['salePrices'] = json_encode($product['salePrices']);
        return $item;
    } else {
        $bundle = $collection->bundle->findOne($filter);
        if (isset($bundle['_id'])) {
            $item['id'] = $bundle['_id'];
            $item['minPrice'] = $bundle['minPrice']['value'];
            $item['salePrices'] = json_encode($bundle['salePrices']);
            return $item;
        } else {
            $service = $collection->service->findOne($filter);
            if (isset($service['_id'])) {
                $item['id'] = $service['_id'];
                $item['minPrice'] = $service['minPrice']['value'];
                $item['salePrices'] = json_encode($service['salePrices']);
                return $item;
            } else {
                return false;
            }
        }
    }

}




function userDataValidation($address, $order)
{
    $err = '';

    $cleanPhone = preg_replace('/\D+/', '', $address['mobile_no']);
    $fullPhone = $address['phone_country'] . $cleanPhone;
    $message = "";

    /*AF3 отсутствует 3 обязательных ФИО*/
    $pieces = explode(" ", $address['contact_person']);
    $fioFail = count(array_filter($pieces));
    if ($fioFail < 3) {
        $message .= "\nФИО, ошибка в заказе #$order";
        $err .= ($fioFail > 0) ? '' : '$fioFail ';
    }

    /*AF3 индекс не соответствует формату -  6 цифр где первые три цифры между 100 и 999*/
    $indexLen = strlen($address['zip']);
    $cityLen = strlen($address['city']);
    $streetLen = strlen($address['detail_address']);
    $address2Len = isset($address['address2']) ? strlen($address['address2']) : 0;
    if ($indexLen != 6 || $cityLen == 0 || $streetLen == 0 || $address2Len == 0) {
        $message .= "\nАдрес, ошибка в заказе #$order";
    }

    /*AF3 3.1 код страны не содержит 7*/
    /*AF3 3.2 телефон не содержит 7 цифр - формат 999 99 99*/
    $countryCode = substr_count($address['phone_country'], "7");
    if ($countryCode < 1 || strlen($cleanPhone) < 10) {
        $message .= "\nТелефонный номер, ошибка в заказе #$order";
        $err .= 'номер телефона err';
    }

    /*  send errors to telegram bot */
    telegram($message, '-278688533');
    return $err;

}

// Parse raw response from ALI for order details
// Create array of order fields
function destructResponse(array $shortener, $order, $shop)
{

    /*  get details from raw response   */
//    $shortener = $res['aliexpress_solution_order_info_get_response']['result']['data'];
    $address = $shortener['receipt_address'];
    $country = $address['country'] == 'RU' ? 'РФ' : $address['country'];
    $address2 = isset($address['address2']) ? $address['address2'] : '';
    $fullAddress = $address['zip'] . ", " . $country . ", " . $address['province'] . ", " . $address['city'] . ", " . $address['detail_address'] . ", " . $address2;

    /*  validate address information    */
    $err = userDataValidation($address, $order);

    /*  add 10 hour shift from PST to RU */
    $offset = 10 * 60 * 60;
    $create_date = date("Y-m-d H:i:s", strtotime($shortener['gmt_create']) + $offset);

    /*  add 4 hours shift for delivery from create_date */
    $offset = 4 * 60 * 60;
    $deliveryPlannedMoment = date("Y-m-d H:i:s", strtotime($create_date) + $offset);

    /*  make array of order details */
    $orderDetails = array();
    $orderDetails['order'] = $order;
    $orderDetails['paid'] = $shortener['fund_status'];
    if (isset($shortener['memo'])) {
        $orderDetails['memo'] = $shortener['memo'];
    }
    $orderDetails['gmt_create'] = $create_date;
    $orderDetails['deliveryPlannedMoment'] = $deliveryPlannedMoment;
    $orderDetails['contact_person'] = $address['contact_person'];
    $orderDetails['phone'] = $address['phone_country'] . $address['mobile_no'];
    $orderDetails['fullAddress'] = $fullAddress;
    $orderDetails['shop'] = $shop;

    $err .= (strlen($fullAddress) > 0) ? '' : ' $fullAddress err';

    /*  get field_id    */
    $field_id = '';
    foreach (LOGINS as $login) {
        if ($login['login'] == $shop) {
            $field_id = $login['field_id'];
            break;
        }

    }
    $orderDetails['field_id'] = $field_id;


    /*  get positions from raw response   */

    $products = $shortener['child_order_list']['global_aeop_tp_child_order_dto'];
    /*  stores all positions    */
    $positions = array();
    $positionsEscrowFee = array();
    $escrowFeeSum = 0;
    $escrowFee = 0;
    $productsCost = 0;
    $orderDiscount = 0;

    try {
        if (is_array($products)) {

            foreach ($products as $product) {

                /*  product result from MS product DB   */
                if (isset($product['sku_code'])) {
                    $product_ms = getProductByIDMongo($product['sku_code']);
                    $product_id = $product_ms['id'];
                    if ($product_id != '') {
                        /*  BF-5  check if sold price less than minimum sell price in MS product DB   */

                        /*minimal product price to sell in MS*/
                        $minPrice = $product_ms['minPrice'];

                        /*actual product price in MS*/
                        $msPriceArray = json_decode($product_ms['salePrices'], true);
                        $msPrice = $msPriceArray[0]["value"];

                        /*product sold by price in Tmall*/
                        $sellPrice = $product['product_price']['cent'];


                        /*get MS stock*/


                        $stockProduct = new \Avaks\MS\Products();
                        $stocks = $stockProduct->getMsStock($product_id);
                        $avaliable_stock = $stocks[0];
                        $productMSName = $stocks[1];

                        if ($avaliable_stock >= $product['product_count']) {
                            $orderDetails['productStocks'][$product_id]['availableMS'] = true;
                        } elseif ($productMSName != '') {
                            /*in case of coupon added to the order*/
                            $orderDetails['productStocks'][$product_id]['availableMS'] = false;
                            telegram("Недостача товара $productMSName. Продано - " . $product['product_count'] . " В МС на момент заказа - $avaliable_stock. Заказ на Tmall № $order.", '-278688533');
                        }


                        /*DISCOUNT CALCULATION BEGINS*/


                        /**
                         * товар1 - 1шт - сумма = 200
                         * товар2 - 2шт -сумма = 100
                         *
                         * купон = 50р
                         *
                         * итого цена товар1 = 200 - 50*(200/300)
                         * цена товар2 = 200 - 50*(100/300)
                         */

                        $productsTotal = 0;
                        $orderAmount = 0;
//                        $discountDiff = 0;
//
//                        /*need this block to calculate Ali clean total positions cost = $productsTotal*/
                        foreach ($products as $item) {
                            $prPrice = $item['product_price']['cent'];
                            $prCo = $item['product_count'];
                            $prTotal = $prPrice * $prCo;
                            $productsTotal += $prTotal;
                            $orderAmount = $shortener['order_amount']['cent'];
                        }
                        echo "init_oder_amount " . $shortener['init_oder_amount']['cent'];
//
//                        /*percentage difference btw Tmall price and MS price - applied discount*/
//                        if ($msPrice > $sellPrice) {
//                            $price = $msPrice;
//                            $tmallTotal = $product['product_count'] * $product['product_price']['cent'];
//                            $discountDiff = ($price - $sellPrice) * 100 / ($price - $sellPrice + $tmallTotal);
//                            var_dump('$discountDiff' . $discountDiff);
//                        } else {
                        $price = $sellPrice;
//                        }
//
//
                        $orderShip = $shortener['logistics_amount']['cent'] ?? 0;
                        $coupon = $productsTotal + $orderShip - $orderAmount;
//                        $msPosition = $msPrice * $product['product_count'];
//                        $aliPosition = $sellPrice * $product['product_count'];
//
//
//                        $totalDisc = ($msPosition - $aliPosition + $coupon) * 100 / ($msPosition - $aliPosition + $productsTotal);
//                        echo "new disc $totalDisc = ($msPosition - $aliPosition + $coupon) * 100 / ($msPosition - $aliPosition + $productsTotal)";
//                        $orderDiscount = ($discountDiff > 0 && $orderDiscount == 0) ? $totalDisc : $orderDiscount;
//
//                        echo $orderDiscount;
//
//
//                        /*not work for MR https://gsp.aliexpress.com/apps/order/detail?spm=5261.order_list.orderTable.2379.55bc3e5fBypimc&orderId=5000481033994782*/
//                        /*heavy solution get all MS prices and calculate discount for each and return final into json*/


                        /*DISCOUNT CALCULATION ENDS*/


                        if ($minPrice > $sellPrice || $msPrice > $sellPrice || $sellPrice > $msPrice) {
                            $sellPrice = number_format($sellPrice / 100, 2, ',', ' ');
                            $msPrice = number_format($msPrice / 100, 2, ',', ' ');
                            $minPrice = number_format($minPrice / 100, 2, ',', ' ');

//                        telegram("Tmall продал товар по неверной цене. Цена продажи $sellPrice. Минимальная цена $minPrice. Цена товарв в МС $msPrice. Заказ на Tmall № $order.", '-278688533');
                        }


                        /*  stores one position */
                        $position = array(
                            "quantity" => $product['product_count'],
                            "price" => $price,
                            "discount" => $orderDiscount,
                            "vat" => 0,
                            "assortment" =>
                                array(
                                    "meta" =>
                                        array(
                                            "href" => "https://online.moysklad.ru/api/remap/1.1/entity/product/$product_id",
                                            "type" => "product",
                                            "mediaType" => "application/json"
                                        ),
                                ),
                            "reserve" => $product['product_count']
                        );

                        /*  gather escrow fee for each product id   */
                        if (isset($product['escrow_fee_rate'])) {
                            $positionEscrowFee = array($product_ms['code'] => $product['escrow_fee_rate']);
                            array_push($positionsEscrowFee, $positionEscrowFee);
                        }
                        $escrowFeeSum += $product['escrow_fee_rate'];


                        array_push($positions, $position);

                        $productsCost += $product['product_price']['cent'] * $product['product_count'];

                    } else {
                        /*  send email  */
                        $productErrorMsg = "Tmall продал товар, которого нет. Заказ на Tmall № $order";
                        $err .= $productErrorMsg;
//                    mail("p.kurskii@avaks.org", "Товар не найден в МС", $productErrorMsg);

                        /*  send errors to telegram bot */
                        telegram($productErrorMsg, '-278688533');
                    }
                } else {
                    $err .= "No sku_code found for $order";
                    telegram("No sku_code found for $order \n", '-320614744');
                }

            }


        } else {
            throw new NotArray();
        }

    } catch (NotArray  $e) {
//        error_log("Caught $e in order $order \n Product list $products");
        telegram("Caught $e in order $order \n", '-320614744');
    }

    $orderDetails['positions'] = json_encode($positions, JSON_UNESCAPED_SLASHES);
    $orderDetails['escrow_fee_rates'] = str_replace(array('%5D', '%5B'), "", http_build_query($positionsEscrowFee, ' ', ','));


    /*ДШ amount*/

    $couponEscrow = $coupon * $escrowFeeSum / sizeof($products);
    $escrowFee = $escrowFeeSum / sizeof($products) * $productsTotal;
    $orderShipEscrow = isset($shortener['logisitcs_escrow_fee_rate']) ? $shortener['logisitcs_escrow_fee_rate'] * $orderShip : 0;
    $orderDetails['dshSum'] = ($escrowFee + $orderShipEscrow - $couponEscrow) / 100;
    $orderDetails['coupon'] = $coupon / 100;
    var_dump("dsh " . $orderDetails['dshSum']);
    var_dump("coupon " . $orderDetails['coupon']);

//    var_dump($orderDetails['productStocks']);
    $orderDetails['err'] = $err;
    if ($orderDetails['err'] != '') {
        telegram($orderDetails['err'] . ". Заказ на Tmall № $order.", '-278688533');
    };

    /*    if ($orderDetails['paid'] != 'PAY_SUCCESS') {
            telegram("Заказ на Tmall № $order. Не оплачен. Ждем оплаты.", '-278688533');
        }*/

//    var_dump($orderDetails);
    /*  fill JSON for order create function */
    fillOrderTemplate($orderDetails);
}


// Get order details
function assembleOrderDetails($order, $filename)
{
    //          then -> get order details from ali_express/ali_order_details.php

    $login = str_replace((array('.txt', 'ali_order_ids/')), "", $filename);
    $sessionKey = '';
    foreach (LOGINS as $constant) {
        if ($constant['login'] == $login) {
            $sessionKey = $constant['sessionKey'];
            $shopEmail = $login;
        }
    }

    /*  request details from ALI by order id    */

    $res = findorderbyid($order, $sessionKey);
    if ($res != false) {
        destructResponse($res, $order, $login);
    } else {
        error_log($res, 3, 'findorderbyid.log');
        telegram("Ошибка API для заказа $order для магазина $shopEmail", "-320614744");
    }


}


// Foreach FILE in folder ali_express/ali_order_ids
define("DIRECTORY", "ali_order_ids/");

//$files = scandir(DIRECTORY);
$files = preg_grep('/^([^.])/', scandir(DIRECTORY));

// Loop through the shop new orders directory

function processOrders($files)
{
    $split = '';
    foreach ($files as $file) {
        //  read rows into array from 2nd line
        $filename = DIRECTORY . $file;
        $fp = fopen($filename, 'r');

        if (strlen($file) > 10) {
            $contents = fread($fp, filesize($filename));
            fclose($fp);
            $remove = "\n";
//        split string into array and
            $split = explode($remove, $contents);

//        remove date from 1st row
            array_shift($split);
        }

        /*  if array NOT empty then -> foreach value in array   */
        if ($split) {


            foreach ($split as $order) {
                if ($order) {
                    /*  check if order id NOT exists in MS  */
//                $link = MS_PATH . "/entity/customerorder/?search=" . $order;
                    $link = MS_PATH . "/entity/customerorder/?filter=name=" . $order;
                    $res = curlMS($link, false, MS_USERNAME);

                    /*    if search result exists */
                    if ($res) {
                        $orderSearchResultArrays = json_decode($res, true);
                        if (!$orderSearchResultArrays['rows']) {
                            /*  no order info found in MS - get order details   */
                            assembleOrderDetails($order, $filename);

                        }
                    }
                }
            }


        }

        //  then NEXT FILE
    }
}



