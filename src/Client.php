<?php
/**
 * Created by PhpStorm.
 * User: lihan
 * Date: 17/2/16
 * Time: 17:08
 */
namespace FSth\DbProxy;

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Connection;

class Client implements ClientExecute
{
    private $dbName;
    private $user;
    private $password;
    private $host;
    private $port;
    private $driver;
    private $charset;

    private $db;

    public function __construct($dbName, $user, $password, $host, $port, $driver = 'pdo_mysql', $charset = 'utf8')
    {
        $this->dbName = $dbName;
        $this->user = $user;
        $this->password = $password;
        $this->host = $host;
        $this->port = $port;
        $this->driver = $driver;
        $this->charset = $charset;
    }

    public function __destruct()
    {
        // TODO: Implement __destruct() method.
        $this->disconnect();
    }

    public function source()
    {
        return $this->db;
    }

    public function connect()
    {
        // TODO: Implement connect() method.
        $this->db = DriverManager::getConnection([
            'dbname' => $this->dbName,
            'user' => $this->user,
            'password' => $this->password,
            'host' => $this->host,
            'port' => $this->port,
            'driver' => $this->driver,
            'charset' => $this->charset
        ]);
    }

    public function disconnect()
    {
        // TODO: Implement disconnect() method.
        try {
            if ($this->valid()) {
                $this->db->close();
            }
        } catch (\Exception $e) {

        } finally {
            $this->db = null;
        }
    }

    public function reconnect()
    {
        // TODO: Implement reconnect() method.
        $this->disconnect();
        $this->connect();
    }

    public function __call($method, $args)
    {
        // TODO: Implement __call() method.
        if ($this->valid()) {
            return call_user_func_array([$this->db, $method], $args);
        }
        throw new \PDOException("execute {$method} failed");
    }

    private function valid()
    {
        return !empty($this->db) && ($this->db instanceof Connection) ? true : false;
    }
}