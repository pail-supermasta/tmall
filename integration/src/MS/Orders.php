<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 21.08.2019
 * Time: 12:19
 */

namespace Avaks\MS;


use Avaks\BackendAPI;


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


        /*{
            '_attributes.#Логистика: агент': "Cainiao",
            _agent: '1b33fbc1-5539-11e9-9ff4-315000060bc8',
            '_attributes.Накладная логистики': {
                $exists: false
            },
            '_attributes.Логистика: Трек': {
                $exists: true
            },
            applicable: true
        }*/

        $collection = (new MSSync())->MSSync;

        $filter = [
            '_attributes.#Логистика: агент' => 'Cainiao',
            '_agent' => '1b33fbc1-5539-11e9-9ff4-315000060bc8',
            '_attributes.Накладная логистики' => ['$exists' => false],
            '_attributes.Логистика: Трек' => ['$exists' => true],
            'applicable' => true
        ];
        $ordersCursor = $collection->customerorder->find($filter);
        $ordersNoSticker = [];

        foreach ($ordersCursor as $orderCursor) {
            $orderElem['trackNum'] = $orderCursor['_attributes']['Логистика: Трек'];
            $orderElem['name'] = $orderCursor['name'];
            $orderElem['externalCode'] = $orderCursor['externalCode'];
            $orderElem['id'] = $orderCursor['_id'];
            $orderElem['description'] = $orderCursor['description'];
            $ordersNoSticker[] = $orderElem;
        }

        return $ordersNoSticker;

    }

    /**
     * check if state Ждем оплаты
     * check not deleted
     * check if has Контрагент Покупатель Тмолл
     */
    public function getWaitPayment()
    {

        /*$query = "SELECT id,`name`,positions,moment
                  FROM `ms_customerorder`
                  WHERE state = '327c0111-75c5-11e5-7a40-e89700139936'
                  AND agent = '1b33fbc1-5539-11e9-9ff4-315000060bc8'
                  AND deleted = ''";
        $ordersWaitPayment = AvaksSQL::selectAllAssoc($query);*/

        $collection = (new MSSync())->MSSync;

        $filter = [
            '_state' => '327c0111-75c5-11e5-7a40-e89700139936',
            '_agent' => '1b33fbc1-5539-11e9-9ff4-315000060bc8',
            'deleted' => ['$exists' => false]
        ];
        $ordersCursor = $collection->customerorder->find($filter);
        $ordersWaitPayment = [];

        foreach ($ordersCursor as $orderCursor) {
            $orderElem['id'] = $orderCursor['_id'];
            $orderElem['name'] = $orderCursor['name'];

            $orderElem['positions'] = $orderCursor['positions']['rows'];
            $orderElem['moment'] = $orderCursor['moment'];
            $ordersWaitPayment[] = $orderElem;
        }

        return $ordersWaitPayment;

    }

    public function getOrdersInWork()
    {
        $backendAPI = new BackendAPI();
        $filter = [
            '_agent' => '1b33fbc1-5539-11e9-9ff4-315000060bc8',
            '_state' => 'ecf45f89-f518-11e6-7a69-9711000ff0c4',
            'applicable' => true,
            '_attributes.#Логистика: агент' => 'Cainiao',
            '_attributes.Логистика: Трек' => ['$exists' => false]
        ];

        $data['filter'] = json_encode($filter);
        $data['limit'] = 9999;
        $data['offset'] = 0;

        $orderCursor = $backendAPI->getData($backendAPI->urlOrder, $data);
        $orderCursor = $orderCursor['rows'];

        return $orderCursor;

    }

    public function getOrdersOnLoad()
    {
        $backendAPI = new BackendAPI();
        $filter = [
            '_agent' => '1b33fbc1-5539-11e9-9ff4-315000060bc8',
            '_state' => ['$in' => [
//                'ecf45f89-f518-11e6-7a69-9711000ff0c4',
//                '327c02b4-75c5-11e5-7a40-e89700139937',
                '8beb227b-6088-11e7-7a6c-d2a9003b81a3',
                '8beb25ab-6088-11e7-7a6c-d2a9003b81a4'
            ]],
            'applicable' => true,
            '_attributes.#Логистика: агент' => 'Cainiao',
            '_attributes.Логистика: Трек' => ['$exists' => true],
            'description' => ['$not' => ['$regex' => 'handover_sheet_passed']]
        ];

        $data['filter'] = json_encode($filter);
        $data['limit'] = 9999;
        $data['offset'] = 0;

        $orderCursor = $backendAPI->getData($backendAPI->urlOrder, $data);
        $orderCursor = $orderCursor['rows'];

        return $orderCursor;

    }

    public function getOrderTEST()
    {
        $backendAPI = new BackendAPI();
        $filter = [
            'name' => '5005385181882110'
        ];

        $data['filter'] = json_encode($filter);
        $data['limit'] = 9999;
        $data['offset'] = 0;

        $orderCursor = $backendAPI->getData($backendAPI->urlOrder, $data);
        $orderCursor = $orderCursor['rows'];

        return $orderCursor;
    }
}