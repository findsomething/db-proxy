<?php

/**
 * Created by PhpStorm.
 * User: lihan
 * Date: 17/2/17
 * Time: 09:43
 */
class ProxyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @database test
     * @table test_table
     * @column
     *  id
     *  test_column
     */

    private $host = '127.0.0.1';
    private $port = 3306;
    private $user = "root";
    private $password = "root";
    private $dbName = "test";

    private $table = "test_table";

    private $value = "test";

    private $client;
    private $proxy;

    public function setUp()
    {
        $this->client = new \FSth\DbProxy\Client($this->dbName, $this->user, $this->password, $this->host, $this->port);
        $this->proxy = new \FSth\DbProxy\Proxy($this->client);
        $this->proxy->setLogger(new FakeLogger());
        $this->proxy->connect();

        $this->truncate();
    }

    public function testInsert()
    {
        $result = $this->insert();
        $this->assertEquals($result['test_column'], $this->value);

        $this->proxy->disconnect();

        $error = "";
        try {
            $result = $this->get($result['id']);
        } catch (\Exception $e) {
            $error = $e->getMessage();
        }
        $this->assertEmpty($error);
        $this->assertNotEmpty($result);

        $otherResult = $this->get($result['id']);
        $this->assertEquals($otherResult['test_column'], $this->value);
    }

    private function truncate()
    {
        $sql = "TRUNCATE {$this->table}";
        $this->proxy->executeQuery($sql);
    }

    private function insert()
    {
        $affected = $this->proxy->insert($this->table, ['test_column' => $this->value]);
        if ($affected <= 0) {
            return [];
        }
        return $this->get($this->proxy->lastInsertId());
    }

    private function get($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ?";
        return $this->proxy->fetchAssoc($sql, [$id]) ?: [];
    }
}

class FakeLogger implements \Psr\Log\LoggerInterface
{
    public function emergency($message, array $context = array())
    {

    }

    public function alert($message, array $context = array())
    {

    }

    public function critical($message, array $context = array())
    {

    }

    public function error($message, array $context = array())
    {

    }

    public function warning($message, array $context = array())
    {

    }

    public function notice($message, array $context = array())
    {

    }

    public function info($message, array $context = array())
    {
    }

    public function debug($message, array $context = array())
    {

    }

    public function log($level, $message, array $context = array())
    {

    }
}