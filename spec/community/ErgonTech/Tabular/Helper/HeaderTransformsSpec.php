<?php

namespace spec\ErgonTech\Tabular;

use Closure;
use ErgonTech\Tabular;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class Helper_HeaderTransformsSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(\ErgonTech\Tabular\Helper_HeaderTransforms::class);
    }

    function it_can_transform_an_input_by_replacing_spaces_with_tabs_and_downcasing()
    {
        $input = 'HELLO WORLD';
        $output = 'hello_world';

        $this->spacesToUnderscoresAndLowercase($input)->shouldReturn($output);
    }

    function it_can_get_the_header_transform_callback_of_a_profile(
        Tabular\Model_Profile $profile,
        \Mage_Core_Model_Config $config
    ) {
        $refMage = new \ReflectionClass(\Mage::class);
        $refConfig = $refMage->getProperty('_config');
        $refConfig->setAccessible(true);
        $refConfig->setValue($config->getWrappedObject());

        $callbackName = headertransform::class . '::blah';
        $profileType = 'profile_type';
        $profile->getProfileType()->willReturn($profileType);
        $transformerName = 'transformer';
        $profile->getExtra('header_transform_callback')->willReturn($transformerName);
        $config->getNode(sprintf('%s/%s/extra/header_transform_callback/options/%s/callback',
                Tabular\Model_Source_Profile_Type::CONFIG_PATH_PROFILE_TYPE,
                $profileType,
                $transformerName))
            ->willReturn($callbackName);

        $output = $this->getHeaderTransformCallbackForProfile($profile);
        $output->shouldHaveType(Closure::class);
        $output->shouldBeCallable();
    }
}

class headertransform { public function blah() {} }
