<?php

namespace spec\ErgonTech\Tabular;

use ErgonTech\Tabular\Model_Source_Profile_Type;
use Mage;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class Model_Source_Profile_TypeSpec extends ObjectBehavior
{
    function let(\Mage_Core_Model_Config $config, ConfigNode $configNode)
    {
        $refMage = new \ReflectionClass(\Mage::class);
        $refConfig = $refMage->getProperty('_config');
        $refConfig->setAccessible(true);
        $refConfig->setValue($config->getWrappedObject());

        $config->getNode(Model_Source_Profile_Type::CONFIG_PATH_PROFILE_TYPE)
            ->willReturn($configNode);

        $configNode->asArray()->willReturn(
            [
                'asdf' => [
                    'class' => 'asdf'
                ]
            ]
        );
    }

    function letGo()
    {
        Mage::reset();
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
        $profileTypes['asdf']['class']->shouldBeLike('asdf');
    }
}

class ConfigNode {
    public function asArray() {}
}
