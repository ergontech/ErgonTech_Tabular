<?php

namespace ErgonTech\Tabular\Step;

use ErgonTech\Tabular\Rows;
use ErgonTech\Tabular\Step;
use ErgonTech\Tabular\StepExecutionException;
use Mage;
use Mage_Eav_Model_Entity_Abstract;
use Mage_Eav_Model_Entity_Attribute_Abstract;
use Mage_Eav_Model_Entity_Attribute_Backend_Array;

/**
 * Given a $classId (`cms/block` or the like), this step iterates over all given $rows.
 * In doing so, it creates a new instance of each $classId and saves it.
 * The returned Rows is the output
 *
 *
 * @author Matthew Wells <matthew@ergon.tech>
 */
class EntityTransformStep implements Step
{
    /**
     * @var Mage_Eav_Model_Entity_Abstract
     */
    private $resourceModel;

    /**
     * EntitySaveStep constructor.
     * @param Mage_Eav_Model_Entity_Abstract $resourceModel
     * @throws StepExecutionException
     */
    public function __construct(Mage_Eav_Model_Entity_Abstract $resourceModel)
    {
        if ($resourceModel instanceof Mage_Eav_Model_Entity_Abstract === false) {
            throw new StepExecutionException('Must provide a valid EAV resouce model!');
        }
        $this->resourceModel = $resourceModel;
    }

    private function isMultiselect($attributeCode)
    {
        $attribute = $this->resourceModel->getAttribute($attributeCode);

        if (!$attribute) {
            return false;
        }

        $backend = $attribute->getBackend();

        if (!$backend) {
            return false;
        }

        if ($backend instanceof Mage_Eav_Model_Entity_Attribute_Backend_Array) {
            return true;
        }

        if ($attribute->getFrontendInput() === 'multiselect') {
            return true;
        }

        return false;
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

        $arrayBackendCodes = array_reduce($rows->getColumnHeaders(), function ($codes, $code) {
            return $this->isMultiselect($code)
                ? array_merge($codes, [$code])
                : $codes;
        }, []);

        /** @var array $transformedRows */
        $transformedRows = array_map(function ($row) use($arrayBackendCodes) {
            foreach ($arrayBackendCodes as $code) {
                if (!empty($row[$code]) && is_string($row[$code])) {
                    $row[$code] = array_filter(array_map('trim', explode(',', $row[$code])));
                }
            }
            return $row;
        }, $rows->getRowsAssoc());

        // The new column headers are the array keys of the newly transformed rows
        $transformedColumnHeaders = array_keys(current($transformedRows));
        $transformedRows = array_map('array_values', $transformedRows);

        return $next(new Rows($transformedColumnHeaders, $transformedRows));
    }
}
