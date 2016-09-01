<?php

namespace ErgonTech\Tabular;

use LogicException;
use Mage_Core_Helper_Abstract;
use Monolog\Handler\HandlerInterface;
use Monolog\Logger;

class Helper_Monolog extends Mage_Core_Helper_Abstract
{
    /**
     * @var Logger[]
     */
    private $loggers = [];

    /**
     * @param $alias
     * @return Logger
     */
    public function registerLogger($alias)
    {
        $logger = $this->getLogger($alias);
        if (is_null($logger)) {
            $logger = $this->loggers[$alias] = new Logger($alias);
        }

        return $logger;
    }

    /**
     * @param $alias
     * @param HandlerInterface $handler
     * @return void
     * @throws \LogicException
     */
    public function pushHandler($alias, HandlerInterface $handler)
    {
        $logger = $this->getLogger($alias);
        if (is_null($logger)) {
            throw new LogicException("Logger '{$alias}' does not exist!");
        }

        $logger->pushHandler($handler);
    }

    /**
     * @param $alias
     * @return Logger|null
     */
    public function getLogger($alias)
    {
        return array_key_exists($alias, $this->loggers)
            ? $this->loggers[$alias]
            : null;
    }
}
