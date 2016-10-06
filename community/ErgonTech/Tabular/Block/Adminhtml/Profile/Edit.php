<?php

namespace ErgonTech\Tabular;

use Mage;
use Mage_Adminhtml_Block_Widget_Form_Container;

class Block_Adminhtml_Profile_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'ergontech_tabular';
        $this->_objectId = 'entity_id';
        $this->_controller = 'adminhtml_profile';

        parent::__construct();

        $runUrl = $this->getUrl('*/*/run');
        $runFieldsetName = Block_Adminhtml_Profile_Edit_Form::RUN_FIELDSET_NAME;

        $this->_formScripts[] = <<<JS
function saveAndContinueEdit() {
    editForm.submit($('edit_form').action+'back/edit/');
}

function runprofile() {
    new Ajax.Request('{$runUrl}', {
        parameters: {
            entity_id: $('entity_id').value
        }, onComplete: function (transport) {
            document.querySelector('#{$runFieldsetName} > div').update('<pre>' + transport.responseText + '</pre>');
        }
    });
}
JS;
    }

    protected function _prepareLayout()
    {
        /** @var Helper_Data $helper */
        $helper = Mage::helper('ergontech_tabular');
        $this->_updateButton('save', 'label', $helper->__('Save Profile'));
        $this->_updateButton('delete', 'label', $helper->__('Delete Profile'));

        $this->_addButton('saveandcontinue', [
            'label' => $helper->__('Save Profile and Continue Edit'),
            'onclick' => 'saveAndContinueEdit()',
            'class' => 'save'
        ], -100);

        if ($this->getProfile()->getProfileType()) {
            $this->_addButton('runprofile', [
                'label' => $helper->__('Run Profile Now'),
                'onclick' => 'runprofile()',
                'class' => 'success'
            ], -101);
        }


        return parent::_prepareLayout();
    }

    public function getHeaderText()
    {
        return Mage::helper('ergontech_tabular')
            ->__($this->getProfile()->getId()
                ? 'Edit Profile' : 'New Profile');
    }


    public function getFormActionUrl()
    {
        if ($this->hasFormActionUrl()) {
            return $this->getData('form_action_url');
        }
        return $this->getUrl('*/*/save');
    }

    /**
     * @return Model_Profile
     */
    protected function getProfile()
    {
        return Mage::registry('ergontech_tabular_profile') ?: Mage::getModel('ergontech_tabular/profile');
    }
}
