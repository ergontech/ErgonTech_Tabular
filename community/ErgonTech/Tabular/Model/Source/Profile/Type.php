<?php

namespace ErgonTech\Tabular;

use Mage;
use Mage_Core_Model_Config_Element;

class Model_Source_Profile_Type
{

    const CONFIG_PATH_PROFILE_TYPE = 'global/ergontech/tabular/profile/type';

    public function toOptionArray()
    {
        $typeArray = $this->getProfileTypes();
        return array_reduce(array_keys($typeArray), function ($retVal, $type) {
            return array_merge($retVal, [[
                'label' => ucwords(str_replace('_', ' ', $type)),
                'value' => $type
            ]]);
        }, []);
    }

    public function getProfileTypes()
    {
        /** @var Mage_Core_Model_Config_Element $node */
        $node = Mage::getConfig()->getNode(static::CONFIG_PATH_PROFILE_TYPE);
        return $node->asArray();
    }



}
