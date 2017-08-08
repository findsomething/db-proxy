<?php

/**
 * Created by PhpStorm.
 * User: lihan
 * Date: 17/2/16
 * Time: 17:47
 */
class ClientTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @database test
     * @table test_table
     * @column
     *  id
     *  test_column
     */

    private $client;

    private $host = '127.0.0.1';
    private $port = 3306;
    private $user = "root";
    private $password = "root";
    private $dbName = "test";

    private $table = "test_table";

    private $value = "test";

    public function setUp()
    {
        $this->client = new \FSth\DbProxy\Client($this->dbName, $this->user, $this->password, $this->host, $this->port);
        $this->client->connect();

        $this->truncate();
    }

    public function testInsert()
    {
        $result = $this->insert();
        $this->assertEquals($result['test_column'], $this->value);

        $this->client->disconnect();
        $error = "";
        try {
            $this->get($result['id']);
        } catch (\Exception $e) {
            $error = $e->getMessage();
        }
        $this->assertNotEmpty($error);

        $this->client->reconnect();
        $otherResult = $this->get($result['id']);
        $this->assertEquals($otherResult['test_column'], $this->value);
    }

    private function truncate()
    {
        $sql = "TRUNCATE {$this->table}";
        $this->client->executeQuery($sql);
    }

    private function insert()
    {
        $affected = $this->client->insert($this->table, ['test_column' => $this->value]);
        if ($affected <= 0) {
            return [];
        }
        return $this->get($this->client->lastInsertId());
    }

    private function get($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ?";
        return $this->client->fetchAssoc($sql, [$id]) ?: [];
    }
}