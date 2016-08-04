<?php

namespace ErgonTech\Tabular\Step\Category;

use ErgonTech\Tabular\Rows;
use ErgonTech\Tabular\Step;
use ErgonTech\Tabular\StepExecutionException;

class FastSimpleImport implements Step
{
    /**
     * @var \AvS_FastSimpleImport_Model_Import
     */
    private $import;

    public function __construct(\AvS_FastSimpleImport_Model_Import $import)
    {
        $this->import = $import;
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
        try {
            $this->import->processCategoryImport($rows->getRowsAssoc());
        } catch (\Exception $e) {
            throw new StepExecutionException($e->getMessage());
        }

        return $rows;
    }
}
