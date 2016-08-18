<?php

namespace ErgonTech\Tabular\Step;

use ErgonTech\Tabular\IteratorStep;
use ErgonTech\Tabular\Model_Profile;
use ErgonTech\Tabular\Rows;
use ErgonTech\Tabular\Step;
use ErgonTech\Tabular\StepExecutionException;
use Mage;

class ProfileStoresToRootCategoriesIterator extends IteratorStep
{
    /**
     * @param string $columnHeader
     * @param Model_Profile $profile
     */
    public function __construct($columnHeader, Model_Profile $profile)
    {
        /** @var \Mage_Core_Model_Resource_Store_Group_Collection $storeGroups */
        $storeGroups = Mage::getResourceModel('core/store_group_collection');
        $storeGroups->join(['cs' => 'core/store'], 'cs.group_id = main_table.group_id', null);
        $storeGroups->getSelect()->where('cs.store_id in (?)', $profile->getStores());

        $rootCategoryIds = array_column($storeGroups->getData(), 'root_category_id');

        /** @var \Mage_Catalog_Model_Resource_Category_Collection $rootCategoryNames */
        $rootCategoryNames = Mage::getResourceModel('catalog/category_collection')
            ->addAttributeToSelect('name')
            ->addIdFilter(array_unique($rootCategoryIds))
            ->getColumnValues('name');

        parent::__construct($rootCategoryNames, $columnHeader);
    }
}
