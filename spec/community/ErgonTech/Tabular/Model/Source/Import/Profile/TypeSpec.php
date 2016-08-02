<?php

namespace spec;

use Mage;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ErgonTech_Tabular_Model_Source_Import_Profile_TypeSpec extends ObjectBehavior
{
    const FAKE_NAME = 'foo';
    const FAKE_CLASS = 'fooclass';

    function let()
    {
        Mage::app();
        Mage::getConfig()->setNode(
            \ErgonTech_Tabular_Model_Source_Import_Profile_Type::CONFIG_PATH_PROFILE_TYPE . '/' . static::FAKE_NAME,
            self::FAKE_CLASS);
    }
    function it_is_initializable()
    {
        $this->shouldHaveType(\ErgonTech_Tabular_Model_Source_Import_Profile_Type::class);
    }

    function it_can_create_an_option_array()
    {
        $optionArray = $this->toOptionArray();
        $optionArray[0]['label']->shouldNotBeNull();
        $optionArray[0]['value']->shouldNotBeNull();
    }

    function it_can_get_an_array_of_types()
    {
        $profileTypes = $this->getProfileTypes();

        $profileTypes->shouldBeArray();
        $profileTypes[static::FAKE_NAME]->shouldBeLike(static::FAKE_CLASS);
    }
}
