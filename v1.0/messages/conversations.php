<?php


// ответить на сообщение в канале https://developers.aliexpress.com/en/doc.htm?docId=31595&docType=2

function curlAli($post_data)
{

    $ALI_URL = 'https://alix.brand.company/api_top3/';
    $ALI_LOGIN = 'NezabudkaMR@yandex.ru';
    $ALI_TOKEN = 'i3j0c3sjtrzclztjp6dsishmt55264dw51rrvep3';
    $ALI_KEY = md5($ALI_LOGIN . gmdate('dmYH') . $ALI_TOKEN);

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
//    var_dump($result);
    $result = json_decode($result, true);

    return $result;
}


$res = curlAli(array(
    'method' => 'aliexpress.message.redefining.versiontwo.querymsgrelationlist',
    'query' => json_encode(array(
        'page_size' => 50,
        'current_page' => 1
    ))
));
echo '<pre>';
print_r($res);
echo '</pre>';