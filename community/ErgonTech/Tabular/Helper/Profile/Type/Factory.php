<?php

namespace ErgonTech\Tabular;

use Exception;
use Mage;
use Mage_Core_Helper_Abstract;

class Helper_Profile_Type_Factory extends Mage_Core_Helper_Abstract
{
    /**
     * @param Model_Profile $profile
     * @return mixed
     * @throws Exception
     */
    public function createProfileTypeInstance(Model_Profile $profile)
    {
        $classname = (string)Mage::getConfig()->getNode(sprintf('%s/%s/class',
            Model_Source_Profile_Type::CONFIG_PATH_PROFILE_TYPE, $profile->getProfileType()));
        if (class_exists($classname)) {
            /** @var Model_Profile_Type $stepTypeInstance */
            $stepTypeInstance = new $classname(new Processor());
            $stepTypeInstance->initialize($profile);
            return $stepTypeInstance;
        }

        throw new Exception_Type($classname . ' is not an existent class');
    }
}
