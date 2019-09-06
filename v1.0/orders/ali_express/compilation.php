<?php

// display results as json
header('Content-Type: application/json');

ini_set('display_errors', 1);

require_once ('../file_processing.php');

//create_date_start - PST (pacific time 10 hours moscow difference)
// russian time
//$offset=10*60*60;
//$loaded_from = date("Y-m-d H:i:s",time()+$offset);


$logins = array(
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
);

//$credentials = $logins[0];


// get order list for store
function curlAli($post_data, $ALI_LOGIN, $ALI_TOKEN)
{


    $ALI_KEY = md5($ALI_LOGIN . gmdate('dmYH') . $ALI_TOKEN);
    $ALI_URL = 'https://alix.brand.company/api_top3/';

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

function formMasterList($credentials)
{

    $login = $credentials['login'];
    $token = $credentials['token'];

//    call cURL
    $create_day = date("Y-m-d");
//    $create_day = "2019-07-16";
    $res = curlAli(array(
        'method' => 'aliexpress.trade.seller.orderlist.get',
        'param_aeop_order_query' => json_encode(array(
            'page_size' => 20,
            'current_page' => 1,
            'create_date_start' => $create_day . ' 00:00:00',
            'order_status_list' => ['WAIT_SELLER_SEND_GOODS', 'PLACE_ORDER_SUCCESS', 'FUND_PROCESSING', 'RISK_CONTROL', 'IN_CANCEL']
        ))
    ), $login, $token);
    return json_decode($res, true);
}


// run formMaster foreach item in logins array
$final = array();

// add shop value
foreach ($logins as $credential) {
    $formed = formMasterList($credential);
    $final[$credential['login']] = $formed;
}


$orderList = json_encode($final);

// search input
$searchStack = json_decode($orderList, true);
// search for key with value in
$lookFor = 'order_id';

function recursiveFind($key, array $searchStack, $lookFor)
{
    // open file ready to write
    $fp = fopen('ali_order_ids/' . $key . '.txt', 'w');

    // write date to file
    $date = new DateTime();
    $date = $date->format("y:m:d h:i:s");
    fwrite($fp, $date . PHP_EOL);

    //recursive part
    $orderIds = array();
    $lastId = 0;
    $iterator = new RecursiveArrayIterator($searchStack);
    $recursive = new RecursiveIteratorIterator(
        $iterator,
        RecursiveIteratorIterator::SELF_FIRST
    );
    foreach ($recursive as $key => $value) {
        if ($key === $lookFor && !is_null($value)) {
            if ($value !== $lastId) {
                array_push($orderIds, $value);

                // check if dublicate
                $lastId = $value;
                // write orderId to file
                fwrite($fp, $value . PHP_EOL);
            }

        }
    }

    //close file
    fclose($fp);

    //end of recursive
    return $orderIds;

}


foreach ($searchStack as $key => $value) {
    recursiveFind($key, $value, $lookFor);
}

processOrders($files);


// main function
//$orderIds = recursiveFind($searchStack, $lookFor);


//echo "<pre>";
//print_r($orderIds);
//echo "</pre>";




