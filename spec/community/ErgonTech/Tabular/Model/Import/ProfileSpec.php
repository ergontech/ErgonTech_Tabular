<?php

namespace spec;

use ErgonTech_Tabular_Exception_Import_Profile as ImportProfileException;
use ErgonTech_Tabular_Model_Import_Profile as ImportProfile;
use ErgonTech_Tabular_Model_Import_Profile as TabularImportProfile;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ErgonTech_Tabular_Model_Import_ProfileSpec extends ObjectBehavior
{
    public function let()
    {
        \Mage::app();
        \Mage::getConfig()->setNode('global/models/ergontech_tabular/resourceModel', 'ergontech_tabular_resource');
        \Mage::getConfig()->setNode('global/models/ergontech_tabular/class', 'ErgonTech_Tabular_Model');
        \Mage::getConfig()->setNode('global/models/ergontech_tabular_resource/class', 'ErgonTech_Tabular_Model_Resource');
    }

    public function letGo()
    {
        \Mage::reset();
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(ImportProfile::class);
    }

    public function it_throws_an_exception_for_an_invalid_name_sent_to_setData()
    {
        $badName = implode('', array_fill(0, ImportProfile::MAX_NAME_LENGTH + 1, 'a'));
        $this->shouldThrow(ImportProfileException::class)->during('setData', ['name', $badName]);
    }

    public function it_throws_an_exception_for_an_invalid_type_id_sent_to_setData()
    {
        $validTypeId = 'only_this_one';
        $validClassName = 'Pretend\Class\Name';
        $badTypeId = 'invalid';

        \Mage::getConfig()->setNode(ImportProfile::XML_PATH_PROFILE_TYPE . '/' . $validTypeId, $validClassName);

        $this->shouldThrow(ImportProfileException::class)->during('setData', ['type_id', $badTypeId]);
    }

    public function it_has_a_resource_model()
    {
        $this->getResource()->shouldReturnAnInstanceOf(\ErgonTech_Tabular_Model_Resource_Import_Profile::class);
    }

    public function it_has_a_resource_collection_model(
        \Zend_Db_Adapter_Abstract $adapter,
        \ErgonTech_Tabular_Model_Resource_Import_Profile $profileResource,
        \Zend_Db_Select $select)
    {
        $adapter->select()->willReturn($select);
        $profileResource->getMainTable()->willReturn('tabular_import_profile');
        $profileResource->getReadConnection()->willReturn($adapter);
        \Mage::register('_resource_singleton/ergontech_tabular/import_profile', $profileResource->getWrappedObject());
        $this->getCollection()->shouldReturnAnInstanceOf(\ErgonTech_Tabular_Model_Resource_Import_Profile_Collection::class);
    }

    public function it_can_load_by_name(
        \ErgonTech_Tabular_Model_Resource_Import_Profile $profileResource,
        TabularImportProfile $profile
    )
    {
        $profileResource
            ->load(Argument::type(TabularImportProfile::class), 'name value', 'name')
            ->willReturn($profile);
        \Mage::register('_resource_singleton/ergontech_tabular/import_profile', $profileResource->getWrappedObject());

        $loadedProfile = $this->loadByName('name value');
        $loadedProfile->shouldHaveType(TabularImportProfile::class);
    }
}
