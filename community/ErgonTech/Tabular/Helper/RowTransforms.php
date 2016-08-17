<?php

namespace ErgonTech\Tabular;

use Mage;

class Helper_RowTransforms extends \Mage_Core_Helper_Abstract
{
    public function returnSelf($value)
    {
        return $value;
    }

    /**
     * Look up in config the row transformation callback configured for this profile
     *
     * @param Model_Profile $profile
     * @return string
     */
    public function getRowTransformCallbackForProfile(Model_Profile $profile)
    {
        $result = Mage::getConfig()->getNode(sprintf('%s/%s/extra/row_transform_callback/options/%s/callback',
            Model_Source_Profile_Type::CONFIG_PATH_PROFILE_TYPE,
            $profile->getProfileType(),
            $profile->getExtra('row_transform_callback')));

        return (string)$result;
    }
}