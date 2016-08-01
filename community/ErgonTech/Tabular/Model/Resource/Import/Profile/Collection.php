<?php

class ErgonTech_Tabular_Model_Resource_Import_Profile_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    public function __construct()
    {
        parent::__construct(Mage::getResourceSingleton('ergontech_tabular/import_profile'));
    }

}
