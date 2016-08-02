<?php

class ErgonTech_Tabular_Block_Adminhtml_Import_Profile_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'ergontech_tabular';
        $this->_objectId = 'entity_id';
        $this->_controller = 'adminhtml_import_profile';

        $helper = Mage::helper('ergontech_tabular');

        parent::__construct();

        $this->_updateButton('save', 'label', $helper->__('Save Import Profile'));
        $this->_updateButton('delete', 'label', $helper->__('Delete Import Profile'));

        $this->_addButton('saveandcontinue', [
            'label' => $helper->__('Save Import Profile and Continue Edit'),
            'onclick' => 'saveAndContinueEdit()',
            'class' => 'save'
        ], -100);

        $this->_formScripts[] = <<<JS
function saveAndContinueEdit() {
    editForm.submit($('edit_form').action+'back/edit/');
}
JS;


    }

}
