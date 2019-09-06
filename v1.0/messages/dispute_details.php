<?php

function curlAli($post_data)
{

    $ALI_URL = 'https://alix.brand.company/api_top3/';
    $ALI_LOGIN = 'NezabudkaND@yandex.ru'; //'novinkiooo@yandex.ru';
    $ALI_TOKEN = 'pnrlgy68ein88wqiz5qsibb0vxnh5d5d81o2lpv2'; //'AVAKS1dji424aa97c-3fc38-4b18-a0ed-12642019G';
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

//params got from dispute_list response
$res = curlAli(array(
    'method' => 'aliexpress.issue.detail.get',
    'buyer_login_id' => 'ru1104476759stqn',
    'issue_id' => '800423245311394',
));
echo '<pre>';
print_r($res);
echo '</pre>';