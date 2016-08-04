<?php

class ErgonTech_Tabular_Helper_HeaderTransforms extends Mage_Core_Helper_Abstract
{
    public static function spacesToUnderscoresAndLowercase($input)
    {
        return str_replace(' ', '_', strtolower($input));
    }

    public static function categoryHeaderMapping($input)
    {
        $mappings = [
            'Root Category' => '_root',
            'Category' => '_category'
        ];

        if (array_key_exists($input, $mappings)) {
            return $mappings[$input];
        }

        return static::spacesToUnderscoresAndLowercase($input);
    }

    public function getHeaderTransformCallbackForProfile(ErgonTech_Tabular_Model_Profile $profile)
    {
        return Mage::getConfig()->getNode(sprintf('%s/%s/extra/header_transform_callback/options/%s/callback',
            ErgonTech_Tabular_Model_Source_Profile_Type::CONFIG_PATH_PROFILE_TYPE,
            $profile->getProfileType(),
            $profile->getExtra('header_transform_callback')));
    }
}
