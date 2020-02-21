<?php
/*During testing, replace values of test parameters with the actual data of the developer's own application.*/

$url = 'https://oauth.aliexpress.com/token';
$postfields = array('grant_type' => 'authorization_code',
    'client_id' => '27862248', //appKey
    'client_secret' => 'ca6916e55a087b3561b5077fc8b83ee6', //appSecret
    'code' => '0_Sjr7NKubGBVOa6E09j9iVjC81293',
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
echo $httpStatusCode;
curl_close($ch);
var_dump($output);

?>