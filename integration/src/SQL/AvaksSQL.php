<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 21.08.2019
 * Time: 12:23
 */

namespace Avaks\SQL;


define('MS_HOST', 'avaks.org');
define('MS_USER', 'avaks');
define('MS_PASS', 'SbTZN8L9fCpVDxtc');
define('MS_DB', 'avaks');


class AvaksSQL
{
    public $sql;


    /**
     * ORDER queries
     */
    public static function selectOrdersByState($query)
    {

        $sql = new \mysqli(MS_HOST, MS_USER, MS_PASS, MS_DB);

        $result = $sql->query($query);

        if ($result->num_rows > 0) {
            $sql->close();
            $ordersAssoc = array();
            while ($row = $result->fetch_assoc()) {
                $ordersAssoc[] = $row;
            }
            return $ordersAssoc;
        } else {
            $sql->close();
            return false;
        }
    }


    /**
     * PRODUCT queries
     */
    public static function selectProductById($id = false)
    {

        $sql = new \mysqli(MS_HOST, MS_USER, MS_PASS, MS_DB);

        $query = "SELECT `index` FROM ms_product WHERE id='$id'";

        $result = $sql->query($query);

        if ($result->num_rows > 0) {

            $sql->close();
            return $result->fetch_assoc()['index'];
        } else {
            $sql->close();
            return false;
        }
    }

    public static function selectAllAssoc($selectQuery)
    {

        $sql = new \mysqli(MS_HOST, MS_USER, MS_PASS, MS_DB);

        $query = $selectQuery;

        $result = $sql->query($query);
        $rows = mysqli_fetch_all($result, MYSQLI_ASSOC);

        if ($result->num_rows > 0) {

            $sql->close();
            return $rows;
        } else {
            $sql->close();
            return false;
        }
    }

}