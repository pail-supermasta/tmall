<?php

require_once 'mysql_class.php';
//ini_set('display_errors', 1);

define('MS_HOST', 'avaks.org');
define('MS_USER', 'avaks');
define('MS_PASS', 'SbTZN8L9fCpVDxtc');
define('MS_DB', 'avaks');

//$settings['telegram_bot_token'] = '345217125:AAE4o7Bs-QeQnusf3SQ-xSuSBm2RGMVH97w';

function getProductIdMS($key, $value)
{
    $row = '';
    $sql = new mysqli(MS_HOST, MS_USER, MS_PASS, MS_DB);
    $query = "SELECT id FROM `ms_product` WHERE attributes LIKE '%" . $key . "\":\"" . $value . "%'";


    $result = $sql->query($query);

    if ($result->num_rows > 0) {
        // output data of each row
        $row = mysqli_fetch_assoc($result);

    }
    return $row["id"];
    // если товар не найден то сообщать об ошибке
    /*  add error handling if product not found   */
}




