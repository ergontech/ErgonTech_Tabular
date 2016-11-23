<?php

namespace ErgonTech\Tabular;

use Mage;
use Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract;

class Block_Adminhtml_System_Config_Form_Field_Array extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
    public function __construct()
    {
        $helper = Mage::helper('ergontech_tabular');
        $this->addColumn('regexp', [
            'label' => $helper->__('Whitelist Regexp'),
            'style' => 'width:120px'
        ]);

        $this->_addButtonLabel = $helper->__('Add Whitelist Regexp');
        $this->_addAfter = false;
        parent::__construct();
    }
}