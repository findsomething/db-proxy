<?php
/**
 * Created by PhpStorm.
 * User: lihan
 * Date: 17/2/16
 * Time: 17:09
 */
namespace Fsth\DbProxy;

interface ClientExecute
{
    function connect();

    function reconnect();

    function disconnect();

    function __call($method, $args);
}