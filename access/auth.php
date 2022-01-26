<?php
$code = $_GET['code'];
header("Content-Type: application/json; charset=UTF-8");


$url = 'https://oauth.aliexpress.com/token';
$postfields = array('grant_type' => 'authorization_code',
    'client_id' => '32817975', //appKey - orion
    'client_secret' => 'fc3e140009f59832442d5c195c807fc0', //appSecret - orion
//    'client_id' => '30833672', //appKey - orion
//    'client_secret' => '1021396785b2eaa1497b7a58dddf19b3', //appSecret - orion
    'code' => $code, //nd
    'sp' => 'ae',
    'redirect_uri' => 'https://tmall-service.a3w.ru/access/auth.html');
$post_data = '';

foreach ($postfields as $key => $value) {
    $post_data .= "$key=" . urlencode($value) . "&";
}
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

//Specify the POST data.
curl_setopt($ch, CURLOPT_POST, true);

//Add variables.
curl_setopt($ch, CURLOPT_POSTFIELDS, substr($post_data, 0, -1));
$output = curl_exec($ch);
$httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
//echo $httpStatusCode;
curl_close($ch);
echo $output;

?>