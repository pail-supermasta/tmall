<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 24.03.2020
 * Time: 12:59
 */

namespace Avaks\MS;


use MongoDB\Client;

class MSSync extends Client
{
    public function __construct(string $uri = 'mongodb://admin:4ae61abd-e1b1-4212-b6a4-1547e70be122-391a65a4-e81c-48a8-9f8d-dc285a2d24ad@mongo-master.backendserver.ru:27017,mongo-slave.backendserver.ru:27017,mongo-arbiter.backendserver.ru:27017/admin?authSource=admin&replicaSet=rsMS&readPreference=primary&appname=MongoDB%20Compass&ssl=false', array $uriOptions = [], array $driverOptions = [])
    {
        parent::__construct($uri, $uriOptions, $driverOptions);
    }
}