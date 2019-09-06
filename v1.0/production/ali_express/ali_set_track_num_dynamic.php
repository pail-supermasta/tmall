<?php


function curlPostTrack($post_data,$login,$token)
{
    $ALI_KEY = md5($login . gmdate('dmYH') . $token);
    $ALI_URL = 'https://alix.brand.company/api_top3/';
    $result = array();
    $headers = array(
        "Content-Type: multipart/form-data",
        "Accept: application/json;charset=UTF-8",
        "Authorization: AccessToken $login:{$ALI_KEY}",
    );

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $ALI_URL);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    $result = curl_exec($curl);
    if (curl_errno($curl)) {
        error_log(curl_errno($curl));
    }
    $search = 'errorCode":"1';
    $found = strpos_recursive($result, $search);
    if ($found) {
        foreach ($found as $pos) {
            error_log('Error ali_set_track_num_dynamic '.substr($result, $pos, 120) . PHP_EOL);
        }
    }
    return $result;
}


/*$res = curlPostTrack(array(
    "method" => "aliexpress.solution.order.fulfill",
    "service_name" => "OTHER_RU_CITY_RUB",
    "tracking_website" => "https://www.pochta.ru/",
//    "out_ref" => "705233044336387",
    "out_ref" => "$orderName",
    "send_type" => "all",
    "logistics_no" => "$trackId"
));*/

