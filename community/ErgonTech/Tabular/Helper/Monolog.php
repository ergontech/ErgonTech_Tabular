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
     * @throws \LogicException
     */
    public function registerLogger($alias)
    {
        if (array_key_exists($alias, $this->loggers)) {
            throw new LogicException('Logger aliases must be unique!');
        }

        $this->loggers[$alias] = new Logger($alias);

        return $this->loggers[$alias];
    }

    /**
     * @param $alias
     * @param HandlerInterface $handler
     * @return void
     * @throws \LogicException
     */
    public function pushHandler($alias, HandlerInterface $handler)
    {
        if (!array_key_exists($alias, $this->loggers)) {
            throw new LogicException("Logger '{$alias}' does not exist!");
        }

        $this->loggers[$alias]->pushHandler($handler);
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
