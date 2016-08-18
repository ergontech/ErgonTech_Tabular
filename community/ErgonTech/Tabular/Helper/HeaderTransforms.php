<?php

namespace ErgonTech\Tabular;

use Mage;
use Mage_Core_Helper_Abstract;

class Helper_HeaderTransforms extends Mage_Core_Helper_Abstract
{
    public function spacesToUnderscoresAndLowercase($input)
    {
        return str_replace(' ', '_', strtolower($input));
    }

    public function categoryHeaderMapping($input)
    {
        $mappings = [
            'Root Category' => '_root',
            'Category' => '_category'
        ];

        if (array_key_exists($input, $mappings)) {
            return $mappings[$input];
        }

        return Mage::helper('ergontech_tabular/headerTransforms')->spacesToUnderscoresAndLowercase($input);
    }

    public function productCategorizationMapping($input)
    {
        $mappings = [
            'SKU' => '_sku',
            'Category' => '_category'
        ];

        if (array_key_exists($input, $mappings)) {
            return $mappings[$input];
        }

        return Mage::helper('ergontech_tabular/headerTransforms')->spacesToUnderscoresAndLowercase($input);
    }

    public function getHeaderTransformCallbackForProfile(Model_Profile $profile)
    {
        $cb = Mage::getConfig()->getNode(sprintf('%s/%s/extra/header_transform_callback/options/%s/callback',
            Model_Source_Profile_Type::CONFIG_PATH_PROFILE_TYPE,
            $profile->getProfileType(),
            $profile->getExtra('header_transform_callback')));

        list($className, $method) = explode('::', $cb);
        $class = new $className;
        $reflectedCb = new \ReflectionMethod($class, $method);

        return $reflectedCb->getClosure($class)->bindTo($profile);
    }
}
