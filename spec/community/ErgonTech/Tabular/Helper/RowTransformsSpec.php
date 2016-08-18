<?php

namespace spec\ErgonTech\Tabular;

use ErgonTech\Tabular\Model_Profile;
use ErgonTech\Tabular\Model_Source_Profile_Type;
use Mage;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class Helper_RowTransformsSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(\ErgonTech\Tabular\Helper_RowTransforms::class);
    }

    function it_gets_the_currently_configured_row_transform(
        \Mage_Core_Model_Config $config,
        Model_Profile $profile
    )
    {
        $profileType = 'asdf';
        $configRowTransform = 'blah';
        $rowTransformCallback = rowtransform::class . '::blah';

        $profile->getProfileType()
            ->willReturn($profileType)
            ->shouldBeCalled();

        $profile->getExtra('row_transform_callback')
            ->willReturn($configRowTransform);

        $refMage = new \ReflectionClass(Mage::class);
        $refConfig = $refMage->getProperty('_config');
        $refConfig->setAccessible(true);
        $refConfig->setValue($config->getWrappedObject());

        $config
            ->getNode(sprintf('%s/%s/extra/row_transform_callback/options/%s/callback',
                Model_Source_Profile_Type::CONFIG_PATH_PROFILE_TYPE,
                $profileType,
                $configRowTransform))
            ->willReturn($rowTransformCallback);

        $cb = $this->getRowTransformCallbackForProfile($profile);
        $cb->shouldHaveType(\Closure::class);
    }
}

class rowtransform { public function blah() {} }
