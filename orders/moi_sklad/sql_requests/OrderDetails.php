<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 02.08.2019
 * Time: 11:59
 */


function getOrderTrack($orderName)
{

    $sql = new mysqli(MS_HOST, MS_USER, MS_PASS, MS_DB);
    /*Отгрузить*/

    $query = "SELECT attributes FROM `ms_customerorder` WHERE `name` = '$orderName'";
//    $query = "SELECT attributes FROM `ms_customerorder` WHERE `name` = '104969913584519'";
    $result = $sql->query($query);
    $rows = mysqli_fetch_all($result);
    $rows = json_decode($rows[0][0], true);

    if (isset($rows['a446677c-46b8-11e7-7a34-5acf0031d7b9'])) {
        $trackId = $rows['a446677c-46b8-11e7-7a34-5acf0031d7b9'];
//        print_r($trackId);
        $sql->close();
        return $trackId;
    } else {
        $sql->close();
        return false;
    }
}


/**
 * check if has state В работе - ecf45f89-f518-11e6-7a69-9711000ff0c4
 * check if has #Логистика: агент 1 Не нужна доставка
 * check if hasn't Track number
 * @param $order
 * @return bool
 */
function checkCainiaoReady($order)
{

    $sql = new mysqli(MS_HOST, MS_USER, MS_PASS, MS_DB);
    $query = "SELECT * FROM ms_customerorder 
              WHERE ms_customerorder.name = '$order' 
              AND state='ecf45f89-f518-11e6-7a69-9711000ff0c4' 
              AND ms_customerorder.attributes LIKE '%6e64fdac-95f9-11e6-7a69-9711000cea11%' 
              AND ms_customerorder.attributes NOT LIKE '%a446677c-46b8-11e7-7a34-5acf0031d7b9%'";
    $result = $sql->query($query);

//    var_dump($result);
    if ($result->num_rows > 0) {

        $sql->close();
        return true;
    } else {
        $sql->close();
        return false;
    }

}


