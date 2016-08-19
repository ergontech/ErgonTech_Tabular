<?php

namespace spec\ErgonTech\Tabular\Model;

use ErgonTech\Tabular\Model\Resource_Profile;
use ErgonTech\Tabular\Model\Resource_Profile_Collection;
use ErgonTech\Tabular\Model_Profile;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use ReflectionClass;

class Resource_Profile_CollectionSpec extends ObjectBehavior
{

    protected $resource;

    private $select;

    function let(
        \Varien_Db_Adapter_Pdo_Mysql $adapter,
        Resource_Profile $resource,
        \Zend_Db_Select $select,
        \Mage_Core_Model_Config $config,
        \Mage_Core_Model_Resource_Helper_Mysql4 $resourceHelperMysql4
    )
    {
        \Mage::register('_resource_helper/core', $resourceHelperMysql4->getWrappedObject());
        $this->resource = $resource;
        $this->select = $select;

        $refMage = new ReflectionClass(\Mage::class);
        $refConfig = $refMage->getProperty('_config');
        $refConfig->setAccessible(true);
        $refConfig->setValue($refMage, $config->getWrappedObject());

        $select->from(['main_table' => 'tabular_profile'])
            ->willReturn($select);
        $select->getPart(\Zend_Db_Select::UNION)
            ->willReturn(null);
        $select->__toString()
            ->willReturn('');
        $select->where(Argument::any(), Argument::any(), Argument::any())->willReturn($select);

        $resource->getMainTable()->willReturn('tabular_profile');
        $resource->getTable('ergontech_tabular/profile_store')->willReturn('asdf');
        $resource->getReadConnection()->willReturn($adapter);
        $resource->getIdFieldName()->willReturn('entity_id');
        $resource->load(Argument::type(Model_Profile::class), null, null)->will(function ($args) {
            list($profile, $id, $field) = $args;

            return $profile;
        });

        $adapter->prepareSqlCondition(Argument::type('string'), Argument::type('array'))->willReturn('');
        $adapter->select()->willReturn($select);
        $adapter->fetchAll(Argument::type('string'), Argument::type('array'))->willReturn([
            [
                'name' => 'hello',
                'entity_id' => '1',
                'profile_type' => 'foo',
                'extra' => serialize(['extra_val' => 'vaaalue'])
            ],
            [
                'name' => 'byebye',
                'entity_id' => '2',
                'profile_type' => 'bar',
                'extra' => serialize(['pbbth' => ':D'])
            ]
        ]);

        $config->getModelClassName('ergontech_tabular/profile')
            ->willReturn('ErgonTech\Tabular\Model_Profile');
        $config->getModelClassName('ErgonTech\Tabular\Model_Profile')
            ->willReturn('ErgonTech\Tabular\Model_Profile');
        \Mage::register('_resource_singleton/ergontech_tabular/profile', $resource->getWrappedObject());
    }

    function letGo()
    {
        \Mage::reset();
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(Resource_Profile_Collection::class);
    }

    function it_gets_profiles_from_the_database()
    {
        $this->load();

        $this->getFirstItem()->shouldHaveType(Model_Profile::class);
        $item2 = $this->getItemById(2);
        $item2->getData('name')->shouldReturn('byebye');
    }

    function it_calls_load_on_each_item()
    {
        $this->resource->load(Argument::type(Model_Profile::class), null, null)->shouldBeCalledTimes(2);
        $this->load();
    }

    function it_can_filter_by_store()
    {
        $storeFilter = 1;
        $this->select
            ->join(
                Argument::type('array'),
                Argument::type('string'),
                Argument::type('array'))
            ->shouldBeCalled()
            ->willReturn($this->select);
        $this->select
            ->group(Argument::type('string'))
            ->shouldBeCalled()
            ->willReturn($this->select);

        $this->addStoreFilter($storeFilter);

        $this->load();
    }

}
