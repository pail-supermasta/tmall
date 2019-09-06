<?php

function findorderbyid($post_data,$login,$token)
{

    //Незабудка ND
    /*$ALI_LOGIN = 'NezabudkaND@yandex.ru';
    $ALI_TOKEN = 'pnrlgy68ein88wqiz5qsibb0vxnh5d5d81o2lpv2';*/

    $ALI_KEY = md5($login . gmdate('dmYH') . $token);
    $ALI_URL = 'https://alix.brand.company/api_top3/';

//    $result = array();

    $headers = array(
        "Content-Type: multipart/form-data",
        "Accept: application/json;charset=UTF-8",
        "Authorization: AccessToken {$login}:{$ALI_KEY}",
    );


    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $ALI_URL);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    $result = curl_exec($curl);
    return json_decode($result,true);
}

//$login='';
//$token='';

/*$res = curlAli(array(
    'method' => 'aliexpress.trade.new.redefining.findorderbyid',
    'param1' => json_encode(array(
        'order_id' => 103892045277693
    ))
),$login,$token);*/

//header('Content-Type: application/json');


//echo $res;
