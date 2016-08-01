<?php

namespace spec;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ErgonTech_Tabular_Model_Resource_Import_Profile_CollectionSpec extends ObjectBehavior
{
    function it_is_initializable(
        \Zend_Db_Adapter_Abstract $adapter,
        \ErgonTech_Tabular_Model_Resource_Import_Profile $resourceImportProfile,
        \Zend_Db_Select $select)
    {
        $adapter->select()->willReturn($select);
        $resourceImportProfile->getMainTable()->willReturn('tabular_import_profile');
        $resourceImportProfile->getReadConnection()->willReturn($adapter);
        \Mage::register('_resource_singleton/ergontech_tabular/import_profile', $resourceImportProfile->getWrappedObject());
        $this->shouldHaveType('ErgonTech_Tabular_Model_Resource_Import_Profile_Collection');
    }
}
