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
    public function __construct(string $uri = 'mongodb://adminUser:1oOfSOh3mTbYhLPx8ypJtx@62.109.13.151:27017/MSSync?authSource=admin', array $uriOptions = [], array $driverOptions = [])
    {
        parent::__construct($uri, $uriOptions, $driverOptions);
    }
}