<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 02.08.2019
 * Time: 11:59
 */

use Avaks\MS\MSSync;




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

    /*{
     filter: {
    _state: 'ecf45f89-f518-11e6-7a69-9711000ff0c4',
      '_attributes.#Логистика: агент': 'Cainiao',
      _agent: '1b33fbc1-5539-11e9-9ff4-315000060bc8',
      '_attributes.Логистика: Трек': {
       $exists: false
      },
      deleted: {
       $exists: false
      }
     }
    }*/

    /*$sql = new mysqli(MS_HOST, MS_USER, MS_PASS, MS_DB);
    $query = "SELECT * FROM ms_customerorder 
              WHERE name = '$order' 
              AND state='ecf45f89-f518-11e6-7a69-9711000ff0c4' 
              AND attributes LIKE '%3071006a-d2db-11e9-0a80-025a0021cd0d%'
			  AND deleted = ''
			  AND agent LIKE '%1b33fbc1-5539-11e9-9ff4-315000060bc8%'  
              AND attributes NOT LIKE '%8a500683-10fc-11ea-0a80-0533000590c8%'";
    $result = $sql->query($query);

    if ($result->num_rows > 0) {

        $sql->close();
        return true;
    } else {
        $sql->close();
        return false;
    }*/

    $collection = (new MSSync())->MSSync;

    $filter = [
        'name' => $order,
        '_state' => 'ecf45f89-f518-11e6-7a69-9711000ff0c4',
        '_attributes.#Логистика: агент' => 'Cainiao',
        '_agent' => '1b33fbc1-5539-11e9-9ff4-315000060bc8',
        '_attributes.Логистика: Трек' => ['$exists' => false],
        'deleted' => ['$exists' => false]
    ];
    $ordersCursor = $collection->customerorder->findOne($filter);

    return isset($ordersCursor['_id']);

}
