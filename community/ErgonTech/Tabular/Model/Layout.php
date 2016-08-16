<?php

class ErgonTech_Tabular_Model_Layout extends Mage_Core_Model_Layout
{
    /**
     * Create block object instance based on block type
     *
     * @param string $block
     * @param array $attributes
     * @return Mage_Core_Block_Abstract
     */
    protected function _getBlockInstance($block, array $attributes=array())
    {
        if (is_string($block)) {
            if (strpos($block, '/')!==false) {
                if (!$block = Mage::getConfig()->getBlockClassName($block)) {
                    Mage::throwException(Mage::helper('core')->__('Invalid block type: %s', $block));
                }
            }
            if (class_exists($block) || mageFindClassFile($block)) {
                $block = new $block($attributes);
            }
        }
        if (!$block instanceof Mage_Core_Block_Abstract) {
            Mage::throwException(Mage::helper('core')->__('Invalid block type: %s', $block));
        }
        return $block;
    }

}