<?php

namespace ErgonTech\Tabular\Step;

use ErgonTech\Tabular\Rows;
use ErgonTech\Tabular\Step;
use ErgonTech\Tabular\StepExecutionException;
use Mage;

/**
 * Given a $classId (`cms/block` or the like), this step iterates over all given $rows.
 * In doing so, it creates a new instance of each $classId and saves it.
 * The returned Rows is the output
 *
 *
 * @author Matthew Wells <matthew@ergon.tech>
 */
class EntitySaveStep implements Step
{
    private $entity;

    public function __construct($classId)
    {
        $this->entity = Mage::getModel($classId);

        if ($this->entity === false) {
            throw new StepExecutionException("Class Id \"{$classId}\" is not valid!");
        }
    }

    /**
     * Accepts a Rows object and returns a rows object
     *
     * @param \ErgonTech\Tabular\Rows $rows
     * @param callable $next
     * @return Rows
     * @throws StepExecutionException
     */
    public function __invoke(Rows $rows, callable $next)
    {
        if (empty($rows->getRows())) {
            return $next($rows);
        }

        /** @var array $transformedEntities */
        $transformedEntities = array_map(function ($row) {
            $entity = clone $this->entity;
            $entity->setData($row);
            $entity->save();
            return $entity->getData();
        }, $rows->getRowsAssoc());

        // The new column headers are the array keys of the newly transformed rows
        $transformedColumnHeaders = array_keys(current($transformedEntities));
        $transformedRows = array_map('array_values', $transformedEntities);

        return $next(new Rows($transformedColumnHeaders, $transformedRows));
    }
}
