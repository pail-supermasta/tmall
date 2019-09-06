<?php

// display results as json
header('Content-Type: application/json');

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
//    $orderIds = array();

//    $offset = 12 * 60 * 60;
//    $create_day = date("Y-m-d H:i:s", strtotime(gmdate("Y-m-d H:i:s")) - $offset);
    $create_day = "2019-07-28";
    $res = curlAli(array(
        'method' => 'aliexpress.trade.seller.orderlist.get',
        'param_aeop_order_query' => json_encode(array(
            'page_size' => 20,
            'current_page' => 1,
            'create_date_start' => $create_day . ' 00:00:00',
//            'create_date_start' => $create_day,
            'order_status_list' => array('WAIT_SELLER_SEND_GOODS', 'PLACE_ORDER_SUCCESS')
        ))
    ), $login, $token);
    return json_decode($res, true);
}



// run formMaster foreach item in logins array
$final = array();

foreach ($logins as $credential) {
//    formMasterList($credential);
    $formed = formMasterList($credential);
    $final[$credential['login']] = $formed;

}

echo json_encode($final);


//var_dump($finalObj);
//echo "<pre>";
//print_r($final);
//echo "</pre>";




