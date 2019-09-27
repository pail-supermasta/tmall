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


/*TEST PART DELETE ATER*/

/*define('APPKEY', '27862248');
define('SECRET', 'ca6916e55a087b3561b5077fc8b83ee6');
require_once 'ali_express/taobao/TopSdk.php';
// Telegram err logs integration
require_once 'class/telegram.php';


$order = '5000456496969609';
$sessionKey = '50002301103yrRc163e29d7ZzgUiKwh0xhbCbrgNTyjFHJHwjSsCd5lSPWxJBdCZLQ5';
$login = 'bestgoodsstore@yandex.ru';

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


function userDataValidation($address, $order)
{

    $cleanPhone = preg_replace('/\D+/', '', $address['mobile_no']);
    $fullPhone = $address['phone_country'] . $cleanPhone;
    $message = "";

    /*AF3 отсутствует 3 обязательных ФИО*/
    $pieces = explode(" ", $address['contact_person']);
    $fioFail = count(array_filter($pieces));
    if ($fioFail < 3) {
        $message .= "\nПо заказу № $order покупатель не верно указал ФИО, ему отправлено сообщение. Тел номер : $fullPhone";
    }

    /*AF3 индекс не соответствует формату -  6 цифр где первые три цифры между 100 и 999*/
    $indexLen = strlen($address['zip']);
    $cityLen = strlen($address['city']);
    $streetLen = strlen($address['detail_address']);
    $address2Len = isset($address['address2']) ? strlen($address['address2']) : 0;
    if ($indexLen != 6 || $cityLen == 0 || $streetLen == 0 || $address2Len == 0) {
        $message .= "\nПо заказу № $order покупатель не верно указал Адрес, ему отправлено сообщение. Тел номер : $fullPhone";
    }

    /*AF3 3.1 код страны не содержит 7*/
    /*AF3 3.2 телефон не содержит 7 цифр - формат 999 99 99*/
    $countryCode = substr_count($address['phone_country'], "7");
    if ($countryCode < 1 || strlen($cleanPhone) < 10) {
        $message .= "\nПо заказу № $order покупатель не верно указал номер телефона.";
    }

    /*  send errors to telegram bot */
    telegram($message, '-278688533');

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
    userDataValidation($address, $order);

    /*  add 10 hour shift from PST to RU */
    $offset = 10 * 60 * 60;
    $create_date = date("Y-m-d H:i:s", strtotime($shortener['gmt_create']) + $offset);

    /*  add 1 day shift for delivery from create_date */
    $offset = 24 * 60 * 60;
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
                $product_ms = getProductIdMS($orderDetails['field_id'], $product['sku_code']);
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
                    $priceArrInt = array();
                    $orderAmount = 0;
                    $discountDiff = 0;
                    $discountNoDiff = 0;

                    foreach ($products as $item) {
                        $prodParam = array();
                        $prPrice = $item['product_price']['cent'];
                        $prCo = $item['product_count'];
                        $prTotal = $prPrice * $prCo;
                        $productsTotal += $prTotal;
                        $orderAmount = $shortener['order_amount']['cent'];

                        $prodParam['count'] = $prCo;
                        $prodParam['price'] = $prPrice;
                        $prodParam['total'] = $prTotal;
                        $priceArrInt[] = $prodParam;
                    }


                    $orderShip = $shortener['logistics_amount']['cent'] ?? 0;
                    $coupon = $productsTotal + $orderShip - $orderAmount;
                    $productPercentDisc = $coupon * 100 / $productsTotal;
                    var_dump('$productPercentDisc' . $productPercentDisc);


                    /*percentage difference btw Tmall price and MS price - applied discount*/
                    /*plus coupon or discount applied*/
                    if ($msPrice > $sellPrice) {
                        $price = $msPrice;
                        $discountDiff = ($price - $sellPrice + $coupon) * 100 / ($price - $sellPrice + $productsTotal);
                        var_dump('$discountDiff' . $discountDiff);
                        $orderDiscount = $discountDiff;
                    } else {
                        $price = $sellPrice;
                        $discountNoDiff = $productPercentDisc;
                        var_dump('$discountNoDiff' . $discountNoDiff);

                    }


                    if ($minPrice > $sellPrice || $msPrice > $sellPrice || $sellPrice > $msPrice) {
                        $sellPrice = number_format($sellPrice / 100, 2, ',', ' ');
                        $msPrice = number_format($msPrice / 100, 2, ',', ' ');
                        $minPrice = number_format($minPrice / 100, 2, ',', ' ');

//                        telegram("Tmall продал товар по неверной цене. Цена продажи $sellPrice. Минимальная цена $minPrice. Цена товарв в МС $msPrice. Заказ на Tmall № $order.", '-278688533');
                    }
                    if ($orderDiscount != 0) {
                        $discount = $orderDiscount;
                    } else {
                        $discount = $discountNoDiff;
                    }
                    var_dump('$orderDiscount' . $orderDiscount);
                    var_dump('$discount' . $discount);

                    /*  stores one position */
                    $position = array(
                        "quantity" => $product['product_count'],
                        "price" => $price,
                        "discount" => $discount,
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
//                    mail("p.kurskii@avaks.org", "Товар не найден в МС", $productErrorMsg);

                    /*  send errors to telegram bot */
//                    telegram($productErrorMsg, '-278688533');
                }
            }


        } else {
            throw new NotArray();
        }

    } catch (NotArray  $e) {
//        error_log("Caught $e in order $order \n Product list $products");
//        telegram("Caught $e in order $order \n", '-320614744');
    }

    $orderDetails['positions'] = json_encode($positions, JSON_UNESCAPED_SLASHES);
    $orderDetails['escrow_fee_rates'] = str_replace(array('%5D', '%5B'), "", http_build_query($positionsEscrowFee, ' ', ','));


    /*ДШ amount*/

//    $orderDetails['dshSum'] = $orderAmount * $escrowFeeSum / sizeof($products) / 100;
    $couponEscrow = $coupon * $escrowFeeSum / sizeof($products);
    $escrowFee = $escrowFeeSum / sizeof($products) * $productsTotal;
    $orderShipEscrow = isset($shortener['logisitcs_escrow_fee_rate']) ? $shortener['logisitcs_escrow_fee_rate'] * $orderShip : 0;
    $orderDetails['dshSum'] = $escrowFee + $orderShipEscrow - $couponEscrow;
    die();

    /*  fill JSON for order create function */
//    fillOrderTemplate($orderDetails);
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
        }
    }

    /*  request details from ALI by order id    */

    $res = findorderbyid($order, $sessionKey);

    destructResponse($res, $order, $login);

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



