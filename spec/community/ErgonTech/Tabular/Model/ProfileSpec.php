<?php

namespace spec\ErgonTech\Tabular;

use ErgonTech\Tabular\Exception_Profile as ProfileException;
use ErgonTech\Tabular\Model\Resource_Profile_Collection;
use ErgonTech\Tabular\Model_Profile;
use ErgonTech\Tabular\Model\Resource_Profile;
use ErgonTech\Tabular\Model_Source_Profile_Type;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class Model_ProfileSpec extends ObjectBehavior
{
    private $profileType;

    public function let(
        \Mage_Core_Model_Config $config,
        Model_Source_Profile_Type $profileType,
        Resource_Profile $resourceProfile,
        Model_Profile $profile,
        Resource_Profile_Collection $resourceProfileCollection)
    {
        $refMage = new \ReflectionClass(\Mage::class);
        $refConfig = $refMage->getProperty('_config');
        $refConfig->setAccessible(true);
        $refConfig->setValue($refMage, $config->getWrappedObject());

        $this->profileType = $profileType;

        $config->getModelInstance('ergontech_tabular/source_profile_type', [])
            ->willReturn($profileType);
        $config->getModelInstance('ergontech_tabular/profile', [])
            ->willReturn($profile);
        $config->getResourceModelInstance('ergontech_tabular/profile', [])
            ->willReturn($resourceProfile);
        $config->getResourceModelInstance('ergontech_tabular/profile_collection', [])
            ->willReturn($resourceProfileCollection);
        $config->getResourceModelInstance('ergontech_tabular/profile_collection', Argument::type(Resource_Profile::class))
            ->willReturn($resourceProfileCollection);

        $profile->load(Argument::type('string'), 'name')
            ->willReturn($profile);

        $profileType->getProfileTypes()
            ->willReturn([]);
    }

    public function letGo()
    {
        \Mage::reset();
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(Model_Profile::class);
    }

    public function it_throws_an_exception_for_an_invalid_name_sent_to_setData()
    {
        $badName = implode('', array_fill(0, Model_Profile::MAX_NAME_LENGTH + 1, 'a'));
        $this->shouldThrow(ProfileException::class)->during('setData', ['name', $badName]);
    }

    public function it_throws_an_exception_for_an_invalid_type_id_sent_to_setData()
    {
        $validTypeId = 'only_this_one';
        $validClassName = 'Pretend\Class\Name';
        $badTypeId = 'invalid';

        $this->profileType->getProfileTypes()->willReturn([$validTypeId => $validClassName]);

        $this->shouldThrow(ProfileException::class)->during('setData', ['type_id', $badTypeId]);
    }

    public function it_has_a_resource_model()
    {
        $this->getResource()->shouldReturnAnInstanceOf(Resource_Profile::class);
    }

    public function it_has_a_resource_collection_model(
        \Zend_Db_Adapter_Abstract $adapter,
        Resource_Profile $profileResource,
        \Zend_Db_Select $select)
    {
        $adapter->select()->willReturn($select);
        $profileResource->getMainTable()->willReturn('tabular_profile');
        $profileResource->getReadConnection()->willReturn($adapter);
        \Mage::register('_resource_singleton/ergontech_tabular/profile', $profileResource->getWrappedObject());
        $this->getCollection()->shouldReturnAnInstanceOf(Resource_Profile_Collection::class);
    }

    public function it_can_load_by_name(
        Resource_Profile $profileResource,
        Model_Profile $profile
    )
    {
        $profileResource
            ->load(Argument::type(Model_Profile::class), 'name value', 'name')
            ->willReturn($profile);
        \Mage::register('_resource_singleton/ergontech_tabular/profile', $profileResource->getWrappedObject());

        $loadedProfile = $this->loadByName('name value');
        $loadedProfile->shouldHaveType(Model_Profile::class);
    }

    public function it_does_not_validate_against_some_keys()
    {
        $this->setData('entity_id', '1')->shouldNotThrow(\Exception::class);
    }
}
