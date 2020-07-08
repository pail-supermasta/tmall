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
