<?php

namespace spec;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ErgonTech_Tabular_Model_Resource_Import_ProfileSpec extends ObjectBehavior
{
    public function let()
    {
        \Mage::app();
    }

    public function letGo()
    {
        \Mage::reset();
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(\ErgonTech_Tabular_Model_Resource_Import_Profile::class);
    }

    public function it_deserializes_extra_data_upon_load(
        \ErgonTech_Tabular_Model_Import_Profile $profile,
        \Mage_Core_Model_Resource $resource,
        \Varien_Db_Adapter_Interface $adapter,
        \Varien_Db_Select $select
    ) {
        $extraValue = ['test_key' => 'value'];
        $serializedExtraValue = serialize($extraValue);
        $dataHash = [
            'name' => 'name value',
            'entity_id' => '1',
            'profile_type' => 'foo',
            'extra' => $serializedExtraValue
        ];

        $resource->getConnection(Argument::type('string'))->willReturn($adapter);
        $resource->getTableName('ergontech_tabular/import_profile')->willReturn('tabular_import_profile');

        $adapter->fetchRow(Argument::type(\Varien_Db_Select::class))->willReturn($dataHash);
        $adapter->getTransactionLevel()->willReturn(0);
        $adapter->quoteIdentifier(Argument::type('string'))->willReturn('');
        $adapter->select()->willReturn($select);

        $select->from(Argument::any())->willReturn($select);
        $select->where(Argument::any(), Argument::any())->willReturn($select);

        \Mage::unregister('_singleton/core/resource');
        \Mage::register('_singleton/core/resource', $resource->getWrappedObject());

        $profile->setData($dataHash)->shouldBeCalled();
        $profile->getData('extra')->willReturn($serializedExtraValue);
        $profile->setData('extra', $extraValue)->shouldBeCalled();

        $this->load($profile, 1);
    }
}
