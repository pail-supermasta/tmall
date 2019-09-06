<?php


//create_date_start - PST (pacific time 10 hours moscow difference)
// russian time
//$offset=10*60*60;
//$loaded_from = date("Y-m-d H:i:s",time()+$offset);


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set("error_log", "../php-error.log");


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

$login = $logins[0]['login'];
$token = $logins[0]['token'];

function strpos_recursive($haystack, $needle, $offset = 0, &$results = array())
{
    $offset = strpos($haystack, $needle, $offset);
    if ($offset === false) {
        return $results;
    } else {
        $results[] = $offset;
        return strpos_recursive($haystack, $needle, ($offset + 1), $results);
    }
}


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


    $search = 'errorCode":"1';
    $found = strpos_recursive($result, $search);
    if ($found) {
        foreach ($found as $pos) {
            error_log(substr($result, $pos, 120) . PHP_EOL);
        }
    }


    return $result;
}

//$create_day= date("Y-m-d");
$create_day = "2019-05-10";
$res = curlAli(array(
    'method' => 'aliexpress.trade.seller.orderlist.get',
    'param_aeop_order_query' => json_encode(array(
        'page_size' => 20,
        'current_page' => 1,
        'create_date_start' => $create_day . ' 00:00:00',
//        'order_status_list' => ['PLACE_ORDER_SUCCESS','FUND_PROCESSING','RISK_CONTROL', 'IN_CANCEL']
    ))
), $login, $token);

header('Content-Type: application/json');

//echo '<pre>';
echo $res . "\n";
//echo '</pre>';