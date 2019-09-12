<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 21.08.2019
 * Time: 15:03
 */

namespace Avaks;


class Customs
{


    public static function findUUID($stack)
    {
        $ID_REGEXP = '/[0-9a-z]{8}-[0-9a-z]{4}-[0-9a-z]{4}-[0-9a-z]{4}-[0-9a-z]{12}/';// Регулярка для UUID
        /*get state of order in MS*/
        preg_match($ID_REGEXP, $stack, $matches);

        if (sizeof($matches) == 1) {
            return $matches[0];
        } else {
            return false;
        }
    }
}