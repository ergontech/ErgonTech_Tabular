<?php

class ErgonTech_Tabular_Block_Adminhtml_Import_Profile_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('import_profile_form');
        $this->setTitle(Mage::helper('ergontech_tabular')->__('Import Profile Information'));
    }

    protected function _prepareForm()
    {
        /** @var ErgonTech_Tabular_Model_Import_Profile $model */
        $model = Mage::registry('ergontech_tabular_import_profile');

        /** @var ErgonTech_Tabular_Helper_Data $helper */
        $helper = Mage::helper('ergontech_tabular');

        /** @var Varien_Data_Form $form */
        $form = new Varien_Data_Form([
            'id' => 'edit_form',
            'action' => $this->getAction(),
            'method' => 'post'
        ]);

        $this->setHtmlIdPrefix('import_profile_');

        /** @var Varien_Data_Form_Element_Fieldset $fieldset */
        $fieldset = $form->addFieldset('base_fieldset', [
            'legend' => $helper->__('General Information'),
            'class' => 'fieldset-wide'
        ]);

        if ($model->getId()) {
            $fieldset->addField('entity_id', 'hidden', [
                'name' => 'entity_id'
            ]);
        }

        $fieldset->addField('name', 'text', [
            'name' => 'name',
            'label' => $helper->__('Import Profile Name'),
            'title' => $helper->__('Import Profile Name'),
            'required' => true,
        ]);

        $fieldset->addField('profile_type', 'select', [
            'name' => 'profile_type',
            'label' => $helper->__('Import Profile Type'),
            'title' => $helper->__('Import Profile Type'),
            'required' => true,
            'options' => array_keys(Mage::getModel('ergontech_tabular/source_import_profile_type')->getProfileTypes())
        ]);

        if (!Mage::app()->isSingleStoreMode()) {
            $field = $fieldset->addField('store_id', 'multiselect', [
                'name'      => 'stores[]',
                'label'     => $helper->__('Store View'),
                'title'     => $helper->__('Store View'),
                'required'  => true,
                'values'    => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true),
            ]);
            $renderer = $this->getLayout()->createBlock('adminhtml/store_switcher_form_renderer_fieldset_element');
            $field->setRenderer($renderer);
        }
        else {
            $fieldset->addField('store_id', 'hidden', array(
                'name'      => 'stores[]',
                'value'     => Mage::app()->getStore(true)->getId()
            ));
            $model->setStoreId(Mage::app()->getStore(true)->getId());
        }

        // TODO: Add `extra` fields

        $this->setForm($form);
        return parent::_prepareForm();
    }


}
