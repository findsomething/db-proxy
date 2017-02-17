<?php
/**
 * Created by PhpStorm.
 * User: lihan
 * Date: 17/2/16
 * Time: 17:08
 */
namespace FSth\DbProxy;

class Proxy implements ProxyExecute
{
    protected $maxReconnectTimes = 3;
    protected $storage;
    protected $logger;
    protected $sleep = true;
    protected $sleepTime = 1;

    public function __construct(Client $storage)
    {
        $this->storage = $storage;
    }

    public function setLogger($logger)
    {
        // TODO: Implement setLogger() method.
        $this->logger = $logger;
    }

    public function __call($method, $args)
    {
        // TODO: Implement __call() method.
        $ok = true;
        $reconnectTimes = 0;

        do {
            try {
                if ($ok == false) {
                    $reconnectTimes++;
                    $this->storage->reconnect();
                    $ok = true;
                }
                return call_user_func_array([$this->storage, $method], $args);
            } catch (\PDOException $e) {
                $this->logger->info('execute error', [
                    'error' => $e->getMessage(),
                    'code' => $e->getCode(),
                    'method' => $method,
                    'args' => $args
                ]);
                if (!$this->checkReconnect()) {
                    throw $e;
                }
                $ok = false;
            }

            if ($reconnectTimes > 1) {
                if ($this->sleep) {
                    sleep($this->sleepTime);
                }
            }
        } while ($ok === false && $reconnectTimes < $this->maxReconnectTimes);

        $this->logger->error("redis reconnect execute error", array(
            'method' => $method,
            'args' => $args,
        ));
    }

    private function checkReconnect()
    {
        $reconnect = false;
        try {
            if ($this->storage->ping() === false) {
                $reconnect = true;
            }
        } catch (\Exception $e) {
            $reconnect = true;
        }
        return $reconnect;
    }
}