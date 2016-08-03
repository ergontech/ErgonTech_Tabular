<?php

class ErgonTech_Tabular_Block_Adminhtml_Profile_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('profile_form');
        $this->setTitle(Mage::helper('ergontech_tabular')->__('Profile Information'));
        $this->setProfile(Mage::registry('ergontech_tabular_profile'));
    }

    protected function _prepareForm()
    {

        /** @var Varien_Data_Form $form */
        $this->setForm(new Varien_Data_Form([
            'id' => 'edit_form',
            'action' => $this->getData('action'),
            'method' => 'post'
        ]));
        $this->setHtmlIdPrefix('profile_');

        $this->addGeneralInformationFieldset();

        if ($this->getProfile()->getId()) {
            $this->addRunFieldset();
        }

        $this->getForm()
            ->setUseContainer(true)
            ->setValues($this->getProfile()->getData());

        $this->addExtraFields();

        parent::_prepareForm();

        return $this;
    }

    protected function addRunFieldset()
    {
        /** @var Varien_Data_Form $form */
        $form = $this->getForm();

        /** @var ErgonTech_Tabular_Model_Profile $profile */
        $profile = $this->getProfile();

        /** @var ErgonTech_Tabular_Helper_Data $helper */
        $helper = Mage::helper('ergontech_tabular');

        /** @var Varien_Data_Form_Element_Fieldset $fieldset */
        $fieldset = $form->addFieldset('run_fieldset', [
            'legend' => $helper->__('Run'),
            'class' => 'fieldset-wide'
        ]);

    }

    /**
     * Add general information to the form's fieldset
     * @return void
     */
    protected function addGeneralInformationFieldset()
    {
        /** @var Varien_Data_Form $form */
        $form = $this->getForm();

        /** @var ErgonTech_Tabular_Model_Profile $profile */
        $profile = $this->getProfile();

         /** @var ErgonTech_Tabular_Helper_Data $helper */
        $helper = Mage::helper('ergontech_tabular');

        /** @var Varien_Data_Form_Element_Fieldset $fieldset */
        $fieldset = $form->addFieldset('base_fieldset', [
            'legend' => $helper->__('General Information'),
            'class' => 'fieldset-wide'
        ]);

        if ($profile->getId()) {
            $fieldset->addField('entity_id', 'hidden', [
                'name' => 'entity_id'
            ]);
        }

        $fieldset->addField('name', 'text', [
            'name' => 'name',
            'label' => $helper->__('Profile Name'),
            'title' => $helper->__('Profile Name'),
            'required' => true,
        ]);

        $profileTypes = array_keys(Mage::getModel('ergontech_tabular/source_profile_type')->getProfileTypes());
        $fieldset->addField('profile_type', 'select', [
            'name' => 'profile_type',
            'label' => $helper->__('Profile Type'),
            'title' => $helper->__('Profile Type'),
            'required' => true,
            'options' => array_combine($profileTypes, $profileTypes)
        ]);

        if (!Mage::app()->isSingleStoreMode()) {
            $field = $fieldset->addField('store_id', 'multiselect', [
                'name' => 'stores[]',
                'label' => $helper->__('Store View'),
                'title' => $helper->__('Store View'),
                'required' => true,
                'values' => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true),
            ]);
            $renderer = $this->getLayout()->createBlock('adminhtml/store_switcher_form_renderer_fieldset_element');
            $field->setRenderer($renderer);
        } else {
            $fieldset->addField('store_id', 'hidden', array(
                'name' => 'stores[]',
                'value' => Mage::app()->getStore(true)->getId()
            ));
            $profile->setStoreId(Mage::app()->getStore(true)->getId());
        }
    }

    public function addExtraFields()
    {
        /** @var ErgonTech_Tabular_Helper_Data $helper */
        $helper = Mage::helper('ergontech_tabular');

        $profile = $this->getProfile();
        /** @var Varien_Data_Form $form */
        $form = $this->getForm();
        $fieldset = $form->getElement('base_fieldset');
        if ($profile->getProfileType()) {
            $extraFields = Mage::getConfig()->getNode(sprintf('%s/%s/extra',
                ErgonTech_Tabular_Model_Source_Profile_Type::CONFIG_PATH_PROFILE_TYPE,
                $profile->getProfileType()));

            foreach ($extraFields->asArray() as $extraField => $fieldConfig) {
                $fieldName = "extra[{$extraField}]";
                /** @var Varien_Data_Form_Element_Abstract $field */
                $field = $fieldset->addField($fieldName, $fieldConfig['input'], [
                    'name' => $fieldName,
                    'label' => $helper->__($fieldConfig['label']),
                    'title' => $helper->__($fieldConfig['label']),
                    'required' => true,
                ]);
                if (array_key_exists('comment', $fieldConfig)) {
                    $field->setData('after_element_html', $fieldConfig['comment']);
                }

                $field->setValue($profile->getExtra($extraField));
            }
        }
    }
}