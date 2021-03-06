<?php
/**
 * Created by PhpStorm.
 * User: lihan
 * Date: 17/2/16
 * Time: 17:08
 */
namespace FSth\DbProxy;

use Doctrine\DBAL\Connection;

class Proxy implements ProxyExecute
{
    const MIN_MICROSECOND = 10;

    protected $maxReconnectTimes = 3;
    protected $storage;
    protected $logger;
    protected $sleep;
    protected $sleepTime;

    public function __construct(Client $storage)
    {
        $this->storage = $storage;

        $this->sleep = true;
        $this->sleepTime = self::MIN_MICROSECOND;
    }

    public function setLogger($logger)
    {
        // TODO: Implement setLogger() method.
        $this->logger = $logger;
    }

    public function setSleepTime($sleepTime)
    {
        $this->sleepTime = max($sleepTime, self::MIN_MICROSECOND);
    }

    public function __call($method, $args)
    {
        // TODO: Implement __call() method.
        $ok = true;
        $reconnectTimes = 0;
        $exception = null;

        do {
            try {
                if (!$ok) {
                    $reconnectTimes++;
                    $this->storage->reconnect();
                    $ok = true;
                }
                return call_user_func_array([$this->storage, $method], $args);
            } catch (DbException $e) {
                $exception = $e;
                if (!$this->needReconnect($e)) {
                    throw $e;
                }
                $ok = false;
            }

            if ($reconnectTimes > 1) {
                if ($this->sleep) {
                    usleep($this->getRandMicroSecond());
                }
            }
            
        } while (!$ok && $reconnectTimes < $this->maxReconnectTimes);

        $this->logger->error("db reconnect execute error", array(
            'method' => $method,
            'args' => $args,
            'error' => $exception->getMessage(),
            'code' => $exception->getCode()
        ));
        throw $exception;
    }

    private function needReconnect(\Exception $e)
    {
        if (strpos($e->getMessage(), "server has gone away") !== false
            || strpos(strtolower($e->getMessage()), 'sqlstate') !== false
        ) {
            return true;
        }
        try {
            if (empty($this->storage->source()) || !($this->storage->source() instanceof Connection) ||
                $this->storage->ping() === false
            ) {
                return true;
            }
        } catch (\Exception $e) {
            return true;
        }
        return false;
    }

    private function getRandMicroSecond()
    {
        return rand(self::MIN_MICROSECOND, $this->sleepTime) * 1000;
    }
}