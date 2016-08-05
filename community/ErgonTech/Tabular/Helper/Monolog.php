<?php

class ErgonTech_Tabular_Helper_Monolog extends Mage_Core_Helper_Abstract
{
    /**
     * @var \Monolog\Logger[]
     */
    private $loggers = [];

    /**
     * @param $alias
     * @return \Monolog\Logger
     * @throws \LogicException
     */
    public function registerLogger($alias)
    {
        if (array_key_exists($alias, $this->loggers)) {
            throw new LogicException('Logger aliases must be unique!');
        }

        $this->loggers[$alias] = new Monolog\Logger($alias);

        return $this->loggers[$alias];
    }

    /**
     * @param $alias
     * @param \Monolog\Handler\HandlerInterface $handler
     * @return void
     * @throws \LogicException
     */
    public function pushHandler($alias, \Monolog\Handler\HandlerInterface $handler)
    {
        if (!array_key_exists($alias, $this->loggers)) {
            throw new LogicException("Logger '{$alias}' does not exist!");
        }

        $this->loggers[$alias]->pushHandler($handler);
    }

    /**
     * @param $alias
     * @return \Monolog\Logger|null
     */
    public function getLogger($alias)
    {
        return array_key_exists($alias, $this->loggers)
            ? $this->loggers[$alias]
            : null;
    }
}
