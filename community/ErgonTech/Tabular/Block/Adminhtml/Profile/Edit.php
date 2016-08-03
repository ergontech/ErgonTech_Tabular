<?php

class ErgonTech_Tabular_Block_Adminhtml_Profile_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'ergontech_tabular';
        $this->_objectId = 'entity_id';
        $this->_controller = 'adminhtml_profile';

        parent::__construct();

        $this->_formScripts[] = <<<JS
function saveAndContinueEdit() {
    editForm.submit($('edit_form').action+'back/edit/');
}
JS;
    }

    protected function _prepareLayout()
    {
        $helper = Mage::helper('ergontech_tabular');
        $this->_updateButton('save', 'label', $helper->__('Save Profile'));
        $this->_updateButton('delete', 'label', $helper->__('Delete Profile'));

        $this->_addButton('saveandcontinue', [
            'label' => $helper->__('Save Profile and Continue Edit'),
            'onclick' => 'saveAndContinueEdit()',
            'class' => 'save'
        ], -100);


        return parent::_prepareLayout();
    }

    public function getHeaderText()
    {
        return Mage::helper('ergontech_tabular')
            ->__(Mage::registry('ergontech_tabular_profile')->getId()
                ? 'Edit Profile' : 'New Profile');
    }


    public function getFormActionUrl()
    {
        if ($this->hasFormActionUrl()) {
            return $this->getData('form_action_url');
        }
        return $this->getUrl('*/*/save');
    }
}