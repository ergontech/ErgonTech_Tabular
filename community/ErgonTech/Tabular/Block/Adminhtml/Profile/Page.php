<?php

namespace ErgonTech\Tabular;

use Mage;
use Mage_Adminhtml_Block_Widget_Grid_Container;

class Block_Adminhtml_Profile_Page extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $dataHelper = Mage::helper('ergontech_tabular');
        $this->_blockGroup = 'ergontech_tabular';
        $this->_controller = 'adminhtml_profile';
        $this->_headerText = $dataHelper->__('Tabular Profiles');
        $this->_addButtonLabel = $dataHelper->__('Add New Profile');
        parent::__construct();
    }
}
