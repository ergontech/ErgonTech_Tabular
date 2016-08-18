<?php

namespace spec\ErgonTech\Tabular;

use ErgonTech\Tabular;
use ErgonTech\Tabular\StepExecutionException;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Mage;


class Helper_Profile_Type_FactorySpec extends ObjectBehavior
{
    private $config;

    public function let(\Mage_Core_Model_Config $config)
    {
        $refMage = new \ReflectionClass(\Mage::class);
        $refConfig = $refMage->getProperty('_config');
        $refConfig->setAccessible(true);
        $refConfig->setValue($config->getWrappedObject());
        $this->config = $config;
    }

    public function letGo()
    {
        Mage::reset();
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(Tabular\Helper_Profile_Type_Factory::class);
    }

    public function it_generates_a_profile_type_instance_given_a_profile_instance(
        Tabular\Model_Profile $profile)
    {

        $modelSuffix = 'foo';
        $modelConfigPath = Tabular\Model_Source_Profile_Type::CONFIG_PATH_PROFILE_TYPE
            . '/' . $modelSuffix
            . '/class';

        $this->config->getNode($modelConfigPath)
            ->willReturn(FactorySpecTestClass::class);

        $profile->getProfileType()->willReturn($modelSuffix);

        $this->createProfileTypeInstance($profile)->shouldReturnAnInstanceOf(FactorySpecTestClass::class);
    }

    public function it_throws_an_exception_when_the_type_instance_cannot_be_found(Tabular\Model_Profile $profile)
    {
        $modelSuffix = 'nope';
        $modelConfigPath = Tabular\Model_Source_Profile_Type::CONFIG_PATH_PROFILE_TYPE
            . '/' . $modelSuffix
            . '/class';

        $this->config->getNode($modelConfigPath)
            ->willReturn('nope_not_a_class_that_exists_i_hope');

        $profile->getProfileType()->willReturn($modelSuffix);

        $this->shouldThrow(Tabular\Exception_Type::class)->during('createProfileTypeInstance', [$profile]);
    }
}

class FactorySpecTestClass implements Tabular\Model_Profile_Type
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
     * @param Tabular\Model_Profile $profile
     * @return void
     */
    public function initialize(Tabular\Model_Profile $profile)
    {
        // TODO: Implement initialize() method.
    }
}