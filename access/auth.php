<?php
$code = $_GET['code'];
header("Content-Type: application/json; charset=UTF-8");


$url = 'https://oauth.aliexpress.com/token';
$postfields = array('grant_type' => 'authorization_code',
    'client_id' => '27862248', //appKey
    'client_secret' => 'ca6916e55a087b3561b5077fc8b83ee6', //appSecret
    'code' => $code, //nd
    'sp' => 'ae',
    'redirect_uri' => 'http://aliexpr.avaks.org/access/auth.html');
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