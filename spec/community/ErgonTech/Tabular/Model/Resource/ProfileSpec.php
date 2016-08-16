<?php

namespace spec\ErgonTech\Tabular\Model;

use ErgonTech\Tabular\Model_Profile as TabularProfile;
use PhpParser\Node\Arg;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class Resource_ProfileSpec extends ObjectBehavior
{
    protected $resource;

    protected $profile;

    protected $adapter;

    protected $select;

    public function let(
        TabularProfile $profile,
        \Mage_Core_Model_Resource $resource,
        \Varien_Db_Adapter_Pdo_Mysql $adapter,
        \Varien_Db_Select $select
    ) {
        \Mage::app();
        $this->profile = $profile;
        $this->resource = $resource;
        $this->adapter = $adapter;
        $this->select = $select;

        $this->profile->getStoreId()->willReturn(null);
        $this->profile->getId()->willReturn(null);


        $this->resource->getConnection(Argument::type('string'))->willReturn($this->adapter);
        $this->resource->getTableName('ergontech_tabular/profile')->willReturn('tabular_profile');
        $this->resource->getTableName('ergontech_tabular/profile_store')->willReturn('tabular_profile_store');

        $this->adapter->getTransactionLevel()->willReturn(0);
        $this->adapter->quoteIdentifier(Argument::type('string'))->willReturn('');
        $this->adapter->select()->willReturn($this->select);
        $this->adapter->fetchRow(Argument::type(\Varien_Db_Select::class))->willReturn([]);

        $this->select->from(Argument::any(), Argument::any())->willReturn($this->select);
        $this->select->where(Argument::any(), Argument::any())->willReturn($this->select);
        $this->select->order(Argument::any())->willReturn($this->select);
        $this->select->limit(Argument::any())->willReturn($this->select);

        \Mage::unregister('_singleton/core/resource');
        \Mage::register('_singleton/core/resource', $this->resource->getWrappedObject());
    }

    public function letGo()
    {
        \Mage::reset();
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(\ErgonTech\Tabular\Model\Resource_Profile::class);
    }

    public function it_deserializes_extra_data_upon_load() {
        $extraValue = ['test_key' => 'value'];
        $serializedExtraValue = serialize($extraValue);
        $dataHash = [
            'name' => 'name value',
            'entity_id' => '1',
            'profile_type' => 'foo',
            'extra' => $serializedExtraValue
        ];
        $this->adapter->fetchRow(Argument::type(\Varien_Db_Select::class))->willReturn($dataHash);

        $this->profile->setData($dataHash)->shouldBeCalled();
        $this->profile->getData('extra')->willReturn($serializedExtraValue);
        $this->profile->setData('extra', $extraValue)->shouldBeCalled();

        $this->load($this->profile, 1);
    }

    public function it_checks_store_association_when_the_profile_has_a_store_id(
        TabularProfile $profile
    )
    {
        $extra = ['asdf' => 'fdas'];
        $serializedExtra = serialize($extra);
        $this->adapter->fetchRow(Argument::type(\Varien_Db_Select::class))->willReturn([
            'name' => 'name value',
            'entity_id' => '1',
            'profile_type' => 'foo',
            'extra' => $serializedExtra
        ]);

        $this->profile->setData(Argument::type('array'))->shouldBeCalled();
        $this->profile->getData('extra')->willReturn($serializedExtra);
        $this->profile->setData('extra', $extra)->shouldBeCalled();

        $profile->getStoreId()->willReturn(1)->shouldBeCalled();
        $this->select->join(
            Argument::type('array'),
            Argument::type('string'),
            Argument::type('array')
        )->willReturn($this->select)->shouldBeCalled();

        $this->load($profile, 1);
    }

    public function it_can_retrieve_associated_store_ids_and_does_so_upon_load()
    {
        $shouldBeStoreIds = [1,2,3];

        $this->profile->getData('extra')->willReturn(null);
        $this->profile->setData('extra', Argument::any())->shouldBeCalled();
        $this->profile->getId()->willReturn(1);
        $this->profile->setData('store_id', $shouldBeStoreIds)->shouldBeCalled();
        $this->profile->setData('stores', $shouldBeStoreIds)->shouldBeCalled();

        $this->resource->getTableName('ergontech_tabular/profile_store')->willReturn('blah');
        $this->adapter->fetchCol(Argument::type(\Varien_Db_Select::class))
            ->willReturn($shouldBeStoreIds);

        $storeIds = $this->lookupStoreIds(1);

        $storeIds->shouldBeArray();
        $storeIds->shouldBeLike($shouldBeStoreIds);

        $this->load($this->profile, 1);
    }

    public function it_saves_store_ids_when_the_entity_is_saved()
    {
        $currentStoreIds = [0];
        $newStoreIds = [1, 0];

        $this->resource->getTableName('ergontech_tabular/profile_store')->willReturn('tabular_profile_store');

        $this->profile->isDeleted()->willReturn(false);
        $this->profile->getData('extra')->willReturn(null);
        $this->profile->setData('extra', Argument::any())->shouldBeCalled();
        $this->profile->getData('stores')->willReturn($newStoreIds);
        $this->profile->getId()->willReturn(1);

        $this->adapter->describeTable(Argument::type('string'))->willReturn(null);
        $this->adapter->update('tabular_profile', Argument::type('array'), Argument::any())->shouldBeCalled();
        $this->adapter->lastInsertId('tabular_profile')->willReturn(1);

        $this->adapter->fetchCol(Argument::type(\Varien_Db_Select::class))
            ->willReturn($currentStoreIds);

        $this->adapter->quoteInto(Argument::any(), Argument::any())->willReturn('');

        $this->adapter->insertMultiple('tabular_profile_store', [
            ['profile_id' => 1, 'store_id' => 1]
        ])->shouldBeCalled();

        $this->save($this->profile);
    }


}
