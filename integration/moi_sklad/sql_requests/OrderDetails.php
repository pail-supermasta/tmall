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

    $query = "SELECT state,attributes FROM `ms_customerorder` WHERE `name` = '$orderName'  AND deleted='' ";
    $result = $sql->query($query);
    $rows = mysqli_fetch_all($result);

    $state = isset($rows[0][0]) ? $rows[0][0] : false;
    if (isset($rows[0][1])) {
        $rows = json_decode($rows[0][1], true);
        $agentId = $rows['4552a58b-46a8-11e7-7a34-5acf002eb7ad'];

        if (isset($rows['8a500683-10fc-11ea-0a80-0533000590c8'])) {
            $trackId = $rows['8a500683-10fc-11ea-0a80-0533000590c8'];
            $sql->close();
            return array('agent' => $agentId, 'track' => $trackId,'state' => $state);
        } else {
            $sql->close();
            return array('agent' => $agentId, 'track' => false, 'state' => $state);
        }
    } else {
        $sql->close();
        return array('agent' => false, 'track' => false,'state' => false);
    }

}


/**
 * check if has state В работе - ecf45f89-f518-11e6-7a69-9711000ff0c4
 * check if has #Логистика: агент Cainiao
 * check not deleted
 * check if has Контрагент Покупатель Тмолл
 * check if hasn't Track number
 * @param $order
 * @return bool
 */
function checkCainiaoReady($order)
{

    $sql = new mysqli(MS_HOST, MS_USER, MS_PASS, MS_DB);
    $query = "SELECT * FROM ms_customerorder 
              WHERE name = '$order' 
              AND state='ecf45f89-f518-11e6-7a69-9711000ff0c4' 
              AND attributes LIKE '%3071006a-d2db-11e9-0a80-025a0021cd0d%'
			  AND deleted = ''
			  AND agent LIKE '%1b33fbc1-5539-11e9-9ff4-315000060bc8%'  
              AND attributes NOT LIKE '%8a500683-10fc-11ea-0a80-0533000590c8%'";
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






