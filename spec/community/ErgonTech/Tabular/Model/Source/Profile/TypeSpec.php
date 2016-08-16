<?php

namespace spec\ErgonTech\Tabular;

use ErgonTech\Tabular\Model_Source_Profile_Type;
use Mage;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class Model_Source_Profile_TypeSpec extends ObjectBehavior
{
    const FAKE_NAME = 'foo';
    const FAKE_CLASS = 'fooclass';

    function let()
    {
        Mage::app();
        Mage::getConfig()->setNode(sprintf('%s/%s/class',
            Model_Source_Profile_Type::CONFIG_PATH_PROFILE_TYPE, static::FAKE_NAME),
            self::FAKE_CLASS);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(Model_Source_Profile_Type::class);
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
        $profileTypes[static::FAKE_NAME]['class']->shouldBeLike(static::FAKE_CLASS);
    }
}
