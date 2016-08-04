<?php

namespace spec;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ErgonTech_Tabular_Helper_HeaderTransformsSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('ErgonTech_Tabular_Helper_HeaderTransforms');
    }

    function it_can_transform_an_input_by_replacing_spaces_with_tabs_and_downcasing()
    {
        $input = 'HELLO WORLD';
        $output = 'hello_world';

        $this->spacesToUnderscoresAndLowercase($input)->shouldReturn($output);
    }

    function it_can_get_the_header_transform_callback_of_a_profile(\ErgonTech_Tabular_Model_Profile $profile)
    {
        \Mage::app();
        $callbackName = 'callback';
        $profileType = 'profile_type';
        $profile->getProfileType()->willReturn($profileType);
        $transformerName = 'transformer';
        $profile->getExtra('header_transform_callback')->willReturn($transformerName);
        \Mage::getConfig()->setNode(
            sprintf('%s/%s/extra/header_transform_callback/options/%s/callback',
                \ErgonTech_Tabular_Model_Source_Profile_Type::CONFIG_PATH_PROFILE_TYPE,
                $profileType,
                $transformerName),
            $callbackName);

        $output = $this->getHeaderTransformCallbackForProfile($profile);
        $output->__toString()->shouldReturn($callbackName);

    }
}
