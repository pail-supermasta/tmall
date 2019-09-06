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


function getOrderTrack($orderId = '705319540636387')
{

    $sql = new mysqli(MS_HOST, MS_USER, MS_PASS, MS_DB);
    /*Отгрузить*/

//    $query = "SELECT attributes FROM `ms_customerorder` WHERE `id` = '$orderId'";
    $query = "SELECT attributes FROM `ms_customerorder` WHERE `name` = '104969913584519'";
    $result = $sql->query($query);
    $rows = mysqli_fetch_all($result);
    $rows = json_decode($rows[0][0], true);

    if (isset($rows['a446677c-46b8-11e7-7a34-5acf0031d7b9'])) {
        $trackId = $rows['a446677c-46b8-11e7-7a34-5acf0031d7b9'];
        print_r($trackId);
        $sql->close();
        return true;
    } else {
        $sql->close();
        return false;
    }
}

getOrderTrack();

