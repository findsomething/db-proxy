<?php
/**
 * Created by PhpStorm.
 * User: lihan
 * Date: 17/2/16
 * Time: 17:26
 */
namespace FSth\DbProxy;

interface ProxyExecute
{
    public function setLogger($logger);

    public function __call($method, $args);
}