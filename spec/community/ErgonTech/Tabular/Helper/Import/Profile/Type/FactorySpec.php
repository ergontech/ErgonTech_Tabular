<?php

namespace spec;

use ErgonTech\Tabular\StepExecutionException;
use ErgonTech_Tabular_Model_Import_Profile;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Mage;

class FactorySpecTestClass implements \ErgonTech_Tabular_Model_Import_Profile_Type
{
    /**
     * Run the profile
     *
     * @return void
     * @throws StepExecutionException
     */
    public function execute()
    {
        // TODO: Implement execute() method.
    }

    /**
     * Initialize the profile type with the given profile instance
     *
     * @param ErgonTech_Tabular_Model_Import_Profile $profile
     * @return void
     */
    public function initialize(ErgonTech_Tabular_Model_Import_Profile $profile)
    {
        // TODO: Implement initialize() method.
    }
}

class ErgonTech_Tabular_Helper_Import_Profile_Type_FactorySpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(\ErgonTech_Tabular_Helper_Import_Profile_Type_Factory::class);
    }

    public function let()
    {
        Mage::app();
    }

    public function letGo()
    {
        Mage::reset();
    }

    public function it_generates_a_profile_type_instance_given_a_profile_instance(\ErgonTech_Tabular_Model_Import_Profile $profile)
    {
        $modelSuffix = 'foo';
        $modelConfigPath = \ErgonTech_Tabular_Model_Import_Profile::XML_PATH_PROFILE_TYPE . '/' . $modelSuffix;

        $config = Mage::getConfig();
        $config->setNode($modelConfigPath, FactorySpecTestClass::class);

        $profile->getType()->willReturn($modelSuffix);

        $this->createProfileTypeInstance($profile)->shouldReturnAnInstanceOf(FactorySpecTestClass::class);
    }

    public function it_throws_an_exception_when_the_type_instance_cannot_be_found(\ErgonTech_Tabular_Model_Import_Profile $profile)
    {
        $modelSuffix = 'nope';
        $modelConfigPath = \ErgonTech_Tabular_Model_Import_Profile::XML_PATH_PROFILE_TYPE . '/' . $modelSuffix;

        $config = Mage::getConfig();
        $config->setNode($modelConfigPath, 'nope_not_a_class_that_exists_i_hope');

        $profile->getType()->willReturn($modelSuffix);

        $this->shouldThrow(\ErgonTech_Tabular_Exception_Type::class)->during('createProfileTypeInstance', [$profile]);
    }
}
