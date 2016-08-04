<?php

namespace ErgonTech\Tabular\Step\Category;

use ErgonTech\Tabular\Rows;
use ErgonTech\Tabular\Step;
use ErgonTech\Tabular\StepExecutionException;

class RootCategoryCreator implements Step
{
    /**
     * @var \Mage_Catalog_Model_Resource_Category_Collection
     */
    protected $collection;

    /**
     * @var string
     */
    protected $rootKey;

    /**
     * RootCategoryCreator constructor.
     * @param \Mage_Catalog_Model_Resource_Category_Collection $collection
     * @param string $rootKey
     */
    public function __construct(
        \Mage_Catalog_Model_Resource_Category_Collection $collection,
        $rootKey = '_root'
    ) {
        $this->collection = $collection;
        $this->rootKey = $rootKey;
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
        $rootCatNames = array_unique(
            array_column($rows->getRows(), array_search($this->rootKey, $rows->getColumnHeaders(), true)));
        $this->collection->addAttributeToFilter('name', ['in' => $rootCatNames]);

        $existingCategoryNames = array_column($this->collection->getData(), 'name');

        $newRootCatNames = array_diff($rootCatNames, $existingCategoryNames);

        array_map(function ($newRootCatName) {
            /** @var \Mage_Catalog_Model_Category $category */
            $category = \Mage::getModel('catalog/category')->setData([
                'name' => $newRootCatName,
                'path' => \Mage_Catalog_Model_Category::TREE_ROOT_ID,
                'is_active' => 1,
                'include_in_menu' => 1
            ]);

            $category->save();

        }, $newRootCatNames);

        return $next($rows);
    }
}
