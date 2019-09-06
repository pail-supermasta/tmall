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
    public function getInWork()
    {
        /*get orders from MS DB*/
        $queryOrderByState = "SELECT id,`name`,positions  
                  FROM `ms_customerorder`  
                  WHERE agent = '64710328-2e6f-11e8-9ff4-34e8000f81c8' 
                  AND state = 'ecf45f89-f518-11e6-7a69-9711000ff0c4' 
                  AND moment > NOW() - INTERVAL 3 DAY 
                  AND deleted=''
                  AND description LIKE '%GOODS1364895%'";
        $ordersByState = AvaksSQL::selectOrdersByState($queryOrderByState);

        $updatedOrdersByState = array();
        $orderByState = array();
        foreach ((array)$ordersByState as $item) {

            $orderMS = new OrderMS($item['id'], $item['name'], $item['positions']);
            $orderByState['name'] = $item['name'];

            $positions = new Products($orderMS->positions);
            $orderByState['positions'] = $positions->getOrderProducts();
            $updatedOrdersByState[] = $orderByState;
        }
        return $updatedOrdersByState;
    }

    public function getOnDelivery()
    {
        /*get orders from MS DB*/
        $queryOrderByState = "SELECT id,`name`,positions  
                  FROM `ms_customerorder`  
                  WHERE agent = '64710328-2e6f-11e8-9ff4-34e8000f81c8' 
                  AND state = '327c03c6-75c5-11e5-7a40-e89700139938' 
                  AND moment > NOW() - INTERVAL 4 DAY 
                  AND deleted=''
                  AND description LIKE '%GOODS1364895%'";
        $ordersByState = AvaksSQL::selectOrdersByState($queryOrderByState);

        $updatedOrdersByState = array();
        $orderByState = array();

        foreach ((array)$ordersByState as $item) {

            $orderMS = new OrderMS($item['id'], $item['name'], $item['positions']);
            $orderByState['name'] = $item['name'];

            $positions = new Products($orderMS->positions);
            $orderByState['positions'] = $positions->getOrderProducts();
            $updatedOrdersByState[] = $orderByState;
        }
        return $updatedOrdersByState;
    }
}