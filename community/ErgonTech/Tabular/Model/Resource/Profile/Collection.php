<?php

namespace ErgonTech\Tabular\Model;

use ErgonTech\Tabular\Model_Profile;
use Mage;
use Mage_Core_Model_Resource_Db_Collection_Abstract;

class Resource_Profile_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * Ensure the resource is available and is the singleton resource
     */
    public function __construct()
    {
        parent::__construct(Mage::getResourceSingleton('ergontech_tabular/profile'));
    }

    /**
     * Initialize the entity type
     */
    protected function _construct()
    {
        $this->_init('ergontech_tabular/profile');
    }

    /**
     * After `load()` cleanup
     *
     * @return $this
     */
    protected function _afterLoad()
    {
        parent::_afterLoad();

        // Iterate over each item and call load on it
        // THIS IS NOT SO BAD.
        // load(null) doesn't touch the database
        foreach ($this->_items as $item) {
            /** @var Model_Profile $item */
            $item->load(null);
        }

        return $this;
    }
    /**
     * Add filter by store
     *
     * @param array|int|\Mage_Core_Model_Store $store
     * @param bool $withAdmin
     * @return Resource_Profile_Collection
     */
    public function addStoreFilter($store, $withAdmin = true)
    {
        if ($store instanceof \Mage_Core_Model_Store) {
            return $this->addStoreFilter($store->getId(), $withAdmin);
        }

        if (!is_array($store)) {
            return $this->addStoreFilter([$store], $withAdmin);
        }

        if ($withAdmin) {
            return $this->addStoreFilter(array_merge($store, [\Mage_Core_Model_App::ADMIN_STORE_ID]), false);
        }

        $this->addFilter('store', ['in' => $store], 'public');
        $this->addFilterToMap('store', 'store_table.store_id');

        return $this;
    }

    /**
     * @return Resource_Profile_Collection
     */
    protected function _renderFiltersBefore()
    {
        if ($this->getFilter('store')) {
            $this->getSelect()->join(
                ['store_table' => $this->getTable('ergontech_tabular/profile_store')],
                'main_table.entity_id = store_table.profile_id',
                []
            )->group('main_table.entity_id');
        }

        return parent::_renderFiltersBefore();
    }
}
