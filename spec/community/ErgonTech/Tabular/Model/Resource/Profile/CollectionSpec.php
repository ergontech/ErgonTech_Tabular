<?php

namespace spec\ErgonTech\Tabular\Model;

use ErgonTech\Tabular\Model\Resource_Profile;
use ErgonTech\Tabular\Model\Resource_Profile_Collection;
use ErgonTech\Tabular\Model_Profile;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class Resource_Profile_CollectionSpec extends ObjectBehavior
{

    protected $resource;

    function let(
        \Zend_Db_Adapter_Abstract $adapter,
        Resource_Profile $resource,
        \Zend_Db_Select $select
    )
    {
        $this->resource = $resource;
        $resource->getMainTable()->willReturn('tabular_profile');
        $resource->getReadConnection()->willReturn($adapter);
        $resource->getIdFieldName()->willReturn('entity_id');
        $this->resource->load(Argument::type(Model_Profile::class), null, null)->will(function ($args) {
            list($profile, $id, $field) = $args;

            return $profile;
        });
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

        \Mage::app();
        \Mage::getConfig()->setNode('global/models/ergontech_tabular/class', 'ErgonTech\Tabular\Model');
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
}
