<?php

namespace spec\ErgonTech\Tabular;

use ErgonTech\Tabular;
use ErgonTech\Tabular\StepExecutionException;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Mage;


class Helper_Profile_Type_FactorySpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(Tabular\Helper_Profile_Type_Factory::class);
    }

    public function let()
    {
    }

    public function letGo()
    {
    }

    public function it_generates_a_profile_type_instance_given_a_profile_instance(Tabular\Model_Profile $profile)
    {
        $modelSuffix = 'foo';
        $modelConfigPath = Tabular\Model_Source_Profile_Type::CONFIG_PATH_PROFILE_TYPE
            . '/' . $modelSuffix
            . '/class';

        $config = Mage::getConfig();
        $config->setNode($modelConfigPath, FactorySpecTestClass::class);

        $profile->getProfileType()->willReturn($modelSuffix);

        $this->createProfileTypeInstance($profile)->shouldReturnAnInstanceOf(FactorySpecTestClass::class);
    }

    public function it_throws_an_exception_when_the_type_instance_cannot_be_found(Tabular\Model_Profile $profile)
    {
        $modelSuffix = 'nope';
        $modelConfigPath = Tabular\Model_Source_Profile_Type::CONFIG_PATH_PROFILE_TYPE
            . '/' . $modelSuffix
            . '/class';

        $config = Mage::getConfig();
        $config->setNode($modelConfigPath, 'nope_not_a_class_that_exists_i_hope');

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