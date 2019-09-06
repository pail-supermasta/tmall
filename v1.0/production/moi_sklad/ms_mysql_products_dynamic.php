<?php

require_once 'mysql_class.php';
//ini_set('display_errors', 1);

define('MS_HOST', 'avaks.org');
define('MS_USER', 'avaks');
define('MS_PASS', 'SbTZN8L9fCpVDxtc');
define('MS_DB', 'avaks');


function getProductIdMS($key, $value)
{
    $row = '';
    $sql = new mysqli(MS_HOST, MS_USER, MS_PASS, MS_DB);
    $query = "SELECT id, minPrice, salePrices, code FROM `ms_product` WHERE code=$value";


    $result = $sql->query($query);

    if ($result->num_rows > 0) {
        // output data of each row
        $row = mysqli_fetch_assoc($result);

    }

    try {
        if ($row) {
//            return $row["id"];
            return $row;
        } else {
            throw new ProductNotFoundMS();
        }

    } catch (ProductNotFoundMS  $e) {
//        error_log("Caught an error when searching for key: $key and id: $value in ms_product");
        telegram("Caught an error when searching for key: $key and id: $value in ms_product", '-320614744');
        return '';
    }

}





