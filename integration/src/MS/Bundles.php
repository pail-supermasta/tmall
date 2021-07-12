<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 21.08.2019
 * Time: 13:16
 */

namespace Avaks\MS;

use  Avaks\MS\MSSync;

class Bundles
{

    public $MSTmallFieldName;

    public function findWithFieldTmall($login)
    {
        $searchStack = array(
//            'NezabudkaMR@yandex.ru' => 'TMall ID (Morphy Richards)',
            'bestgoodsstore@yandex.ru' => 'TMall ID (Best Goods)',
            'orionstore360@gmail.com' => 'TMall ID (Orion)',
//            'NezabudkaiRobot@yandex.ru' => 'TMall ID (iRobot)',
//            'NezabudkaND@yandex.ru' => 'TMall ID (Noerden)',
//            'novinkiooo@yandex.ru' => 'TMall ID'
        );
        $collection = (new MSSync())->MSSync;

        $filter = ['_attributes.' . $searchStack[$login] => ['$exists' => true]];
        $productCursor = $collection->bundle->find($filter)->toArray();
        $this->MSTmallFieldName = $searchStack[$login];
        return $productCursor;
    }

}
