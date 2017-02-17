<?php
/**
 * Created by PhpStorm.
 * User: lihan
 * Date: 17/2/16
 * Time: 17:09
 */
namespace Fsth\DbProxy;

use Doctrine\DBAL\Connection;

interface ClientExecute
{
    function connect();

    function reconnect();

    function disconnect();

    /**
     * @return Connection
     */
    function source();

    function __call($method, $args);
}