<?php

class ErgonTech_Tabular_Model_Resource_Profile extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Resource initialization
     */
    protected function _construct()
    {
        $this->_init('ergontech_tabular/profile', 'entity_id');
        $this->_serializableFields = [
            'extra' => [null, []]
        ];
    }
}
