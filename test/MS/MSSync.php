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
    public function __construct(string $uri = 'mongodb://MSSync-read:1547e70be122dc285a2d24ad@23.105.225.41:27017/?authSource=MSSync&readPreference=primary&appname=MongoDB%20Compass&ssl=false', array $uriOptions = [], array $driverOptions = [])
    {
        parent::__construct($uri, $uriOptions, $driverOptions);
    }
}