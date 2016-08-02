<?php

class ErgonTech_Tabular_Helper_Import_Profile_Type_Factory extends Mage_Core_Helper_Abstract
{
    /**
     * @param ErgonTech_Tabular_Model_Import_Profile $profile
     * @return mixed
     * @throws Exception
     */
    public function createProfileTypeInstance(ErgonTech_Tabular_Model_Import_Profile $profile)
    {
        $classname = (string)Mage::getConfig()->getNode(sprintf('%s/%s',
            ErgonTech_Tabular_Model_Source_Import_Profile_Type::CONFIG_PATH_PROFILE_TYPE, $profile->getType()));
        if (class_exists($classname)) {
            /** @var ErgonTech_Tabular_Model_Import_Profile_Type $stepTypeInstance */
            $stepTypeInstance = new $classname(new ErgonTech\Tabular\Processor());
            $stepTypeInstance->initialize($profile);
            return $stepTypeInstance;
        }

        throw new ErgonTech_Tabular_Exception_Type($classname . ' is not an existent class');
    }
}