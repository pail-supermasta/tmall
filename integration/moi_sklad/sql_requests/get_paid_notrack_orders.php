<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 02.08.2019
 * Time: 11:59
 */

define('MS_HOST', 'avaks.org');
define('MS_USER', 'avaks');
define('MS_PASS', 'SbTZN8L9fCpVDxtc');
define('MS_DB', 'avaks');


function getPaidNoTrackOrder($order)
{

    $sql = new mysqli(MS_HOST, MS_USER, MS_PASS, MS_DB);
    $query = "SELECT * FROM `ms_customerorder`  WHERE `name`='$order' and state='327c02b4-75c5-11e5-7a40-e89700139937' AND attributes  LIKE '%\"8a500683-10fc-11ea-0a80-0533000590c8\":%'";
    $result = $sql->query($query);

    var_dump($result);
    if ($result->num_rows > 0) {

        $sql->close();
        return true;
    } else {
        $sql->close();
        return false;
    }

}





