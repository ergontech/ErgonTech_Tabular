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

}
