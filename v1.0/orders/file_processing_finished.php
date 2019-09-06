<?php

ini_set('display_errors', 1);

header('Content-Type: application/json');




define('LOGINS', array(
    array(
        'name' => 'Новинки',
        'login' => 'novinkiooo@yandex.ru',
        'field_id' => 'e8a40577-77b9-11e9-912f-f3d40003d45d',
        'token' => 'AVAKS1dji424aa97c-3fc38-4b18-a0ed-12642019G',
    ),
    array(
        'name' => 'bestgoodsstore',
        'login' => 'bestgoodsstore@yandex.ru',
        'field_id' => '0bbcd3e6-81f4-11e9-9109-f8fc0004dec8',
        'token' => '2eb8s5gt3b8554viida367-34be7205fd9d',
    ),
    array(
        'name' => 'Незабудка ND',
        'login' => 'NezabudkaND@yandex.ru',
        'field_id' => '0bbce15c-81f4-11e9-9109-f8fc0004decb',
        'token' => 'pnrlgy68ein88wqiz5qsibb0vxnh5d5d81o2lpv2',
    ),
    array(
        'name' => 'Незабудка MR',
        'login' => 'NezabudkaMR@yandex.ru',
        'field_id' => '0bbcd991-81f4-11e9-9109-f8fc0004dec9',
        'token' => 'i3j0c3sjtrzclztjp6dsishmt55264dw51rrvep3',
    ),
    array(
        'name' => 'Незабудка iRobot',
        'login' => 'NezabudkaiRobot@yandex.ru',
        'field_id' => '0bbcde02-81f4-11e9-9109-f8fc0004deca',
        'token' => 'jizsgu7g09sw02tkpdj85by0wgthofgpkv7re2k7',
    )
));

// Parse raw response from ALI for order details
// Create array of order fields
function destructResponse($res, $order, $shop)
{


    /*  get details from raw response   */
    $shortener = $res['aliexpress_trade_new_redefining_findorderbyid_response']['target'];
    $address = $shortener['receipt_address'];
    $country = $address['country'] == 'RU' ? 'РФ' : $address['country'];
    $fullAddress = $address['zip'] . ", " . $country . ", " . $address['province'] . ", " . $address['city'] . ", " . $address['detail_address'] . ", " . $address['address2'];


    /*  add 1 hour shift for delivery */
    $offset = 24 * 60 * 60;
    $deliveryPlannedMoment = date("Y-m-d H:i:s", strtotime($shortener['gmt_create']) + $offset);


    /*  make array of order details */
    $orderDetails = array();
    $orderDetails['order'] = $order;
    $orderDetails['paid'] = $shortener['fund_status'];
    $orderDetails['gmt_create'] = $shortener['gmt_create'];
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

    $products = $shortener['child_order_ext_info_list']['aeop_tp_order_product_info_dto'];
    /*  stores all positions    */
    $positions = array();
    foreach ($products as $product) {

        $product_id = getProductIdMS($orderDetails['field_id'], $product['product_id']);

        /*  stores one position */
        $position = array(
            "quantity" => $product['quantity'],
            "price" => $product['unit_price']['cent'],
            "discount" => 0,
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
            "reserve" => $product['quantity']
        );

        array_push($positions, $position);
    }
    $orderDetails['positions'] = json_encode($positions, JSON_UNESCAPED_SLASHES);

    /*  fill JSON for order create function */
    fillOrderTemplate($orderDetails);
}


// Get order details
function assembleOrderDetails($order, $filename)
{
    //          then -> get order details from ali_express/ali_order_details.php

    $login = str_replace((array('.txt', 'ali_order_ids/')), "", $filename);
    $token = '';
    foreach (LOGINS as $constant) {
        if ($constant['login'] == $login) {
            $token = $constant['token'];
        }
    }


    /*  request details from ALI by order id    */
    $res = findorderbyid(array(
        'method' => 'aliexpress.trade.new.redefining.findorderbyid',
        'param1' => json_encode(array(
            'order_id' => $order
        ))
    ), $login, $token);

    destructResponse($res, $order, $login);

    //           write into array where key = order_id and value = array (order_details)
    //  when all new order details gathered into array
    //  foreach row in array -> create new order in MS
    //  clear array
}


// Foreach FILE in folder ali_express/ali_order_ids
define("DIRECTORY", "ali_order_ids/");

$files = scandir(DIRECTORY);

// Loop through the shop new orders directory

function processOrders($files)
{
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
//                        var_dump($order);
                            /*  no order info found in MS - get order details   */
                            assembleOrderDetails($order, $filename);
                        }

                    }

                }
//
            }
        }


        //  then NEXT FILE
    }
}



