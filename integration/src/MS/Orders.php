<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 21.08.2019
 * Time: 12:19
 */

namespace Avaks\MS;


use Avaks\SQL\AvaksSQL;
use Avaks\MS\OrderMS;
use Avaks\MS\Products;

class Orders
{

    /**
     * check if has #Логистика: агент Cainiao
     * check not deleted
     * check if has Контрагент Покупатель Тмолл
     * check if has Track number
     * check if hasn't Sticker pdf
     */
    public function getOrdersNoSticker()
    {

        $query = "SELECT attributes,`name`,externalCode,id,description FROM ms_customerorder 
              WHERE attributes LIKE '%3071006a-d2db-11e9-0a80-025a0021cd0d%'
			  AND deleted = ''
			  AND agent LIKE '%1b33fbc1-5539-11e9-9ff4-315000060bc8%'  
              AND attributes LIKE '%8a500683-10fc-11ea-0a80-0533000590c8%'
              AND attributes NOT LIKE '%b8a8f6d6-5782-11e8-9ff4-34e800181bf6%'";
        $ordersNoSticker = AvaksSQL::selectAllAssoc($query);
        return $ordersNoSticker;

    }

    /**
     * check if state Ждем оплаты
     * check not deleted
     * check if has Контрагент Покупатель Тмолл
     */
    public function getWaitPayment()
    {

        $query = "SELECT id,`name`,positions,moment
                  FROM `ms_customerorder`
                  WHERE state = '327c0111-75c5-11e5-7a40-e89700139936'
                  AND agent = '1b33fbc1-5539-11e9-9ff4-315000060bc8'
                  AND deleted = ''";
        $ordersNoSticker = AvaksSQL::selectAllAssoc($query);
        return $ordersNoSticker;

    }
}