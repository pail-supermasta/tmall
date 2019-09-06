<?php
ini_set('display_errors', 1);
function curlAli($post_data)
{

    //Незабудка ND
//    $ALI_LOGIN = 'bestgoodsstore@yandex.ru';
    $ALI_LOGIN = 'NezabudkaMR@yandex.ru';
    $ALI_TOKEN = 'i3j0c3sjtrzclztjp6dsishmt55264dw51rrvep3';

    $ALI_KEY = md5($ALI_LOGIN . gmdate('dmYH') . $ALI_TOKEN);
    $ALI_URL = 'https://alix.brand.company/api_top3/';

    $result = array();

    $headers = array(
        "Content-Type: multipart/form-data",
        "Accept: application/json;charset=UTF-8",
        "Authorization: AccessToken {$ALI_LOGIN}:{$ALI_KEY}",
    );


    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $ALI_URL);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    $result = curl_exec($curl);
    return $result;
}


$res = curlAli(array(
//    'method' => 'aliexpress.trade.new.redefining.findorderbyid',
    'method' => 'aliexpress.solution.order.info.get',
    'param1' => json_encode(array(
        'order_id' => 105193827375776
    ))
));

header('Content-Type: application/json');


echo $res;

/*$res = json_decode($res,true);
$shortener = $res['aliexpress_solution_order_info_get_response']['result']['data'];
$products = $shortener['child_order_list']['global_aeop_tp_child_order_dto'];*/

/*foreach ($products as $product) {
    $position = array(
        "quantity" => $product['quantity'],

        "vat" => 0,
        "assortment" =>
            array(
                "meta" =>
                    array(
//                        "href" => "https://online.moysklad.ru/api/remap/1.1/entity/product/$product_id",
                        "type" => "product",
                        "mediaType" => "application/json"
                    ),
            ),
        "reserve" => $product['quantity']
    );

    echo $product['quantity'];
    echo (float)$product['quantity'];
}*/
