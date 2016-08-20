<?php

namespace spec\ErgonTech\Tabular;

use ErgonTech\Tabular;
use Mage;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;


class Block_Adminhtml_Profile_Edit_FormSpec extends ObjectBehavior
{
    private $form;

    private $refThis;

    private $profile;

    function let(
        Tabular\Helper_Data $data,
        \Varien_Data_Form $form,
        \Mage_Core_Model_Config $configModel,
        \Mage_Core_Model_Store $store,
        \Mage_Core_Model_App $app,
        Tabular\Model_Profile $profile,
        Tabular\Model_Source_Profile_Type $tabularProfileTypeSource,
        \Varien_Data_Form_Element_Fieldset $fieldset,
        \Varien_Data_Form_Element_Abstract $element,
        \Mage_Adminhtml_Model_System_Store $adminhtmlSystemStore,
        \Mage_Core_Model_Layout $layout,
        \Mage_Adminhtml_Block_Store_Switcher_Form_Renderer_Fieldset_Element $storeSwitcherElement,
        ugh $configElement
    )
    {
        $this->profile = $profile;

        Mage::register('_singleton/adminhtml/system_store', $adminhtmlSystemStore->getWrappedObject());
        Mage::register('ergontech_tabular_profile', $this->profile->getWrappedObject());
        Mage::register('_helper/ergontech_tabular', $data->getWrappedObject());
        Mage::register('_helper/ergontech_tabular/data', $data->getWrappedObject());


        $configModel->getModelInstance(\Varien_Data_Form::class, Argument::type('array'))
            ->willReturn($form->getWrappedObject());

        $configModel->getModelInstance('core/store', Argument::type('array'))
            ->willReturn($store->getWrappedObject());

        $configModel->getModelInstance('ergontech_tabular/source_profile_type', Argument::type('array'))
            ->willReturn($tabularProfileTypeSource->getWrappedObject());

        $configModel->getNode(Argument::type('string'))->willReturn($configElement);

        $store->setId(Argument::type('numeric'))
            ->willReturn($store);
        $store->setCode(Argument::type('string'))
            ->willReturn($store);
        $store->getBaseUrl('link', null)->willReturn('pbbth');

        $tabularProfileTypeSource->getProfileTypes()->willReturn([
            'asdf' => 'asdf',
            'fdsa' => 'fdas'
        ]);

        $form->addFieldset(Argument::type('string'), Argument::type('array'))
            ->willReturn($fieldset);
        $form->setValues(Argument::type('array'))->willReturn($form);
        $this->form = $form;

        $fieldset->addField(Argument::type('string'), Argument::type('string'), Argument::type('array'))
            ->willReturn($element);

        $layout->createBlock('adminhtml/store_switcher_form_renderer_fieldset_element')
            ->willReturn($storeSwitcherElement);

        $this->refThis = new \ReflectionClass($this->getWrappedObject());
        $refLayout = $this->refThis->getProperty('_layout');
        $refLayout->setAccessible(true);
        $refLayout->setValue($this->getWrappedObject(), $layout->getWrappedObject());

        $refMage = new \ReflectionClass(Mage::class);
        $refConfig = $refMage->getProperty('_config');
        $refConfig->setAccessible(true);
        $refConfig->setValue($refMage, $configModel->getWrappedObject());
    }

    function letGo()
    {
        Mage::reset();
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(Tabular\Block_Adminhtml_Profile_Edit_Form::class);
    }

    function it_prepares_the_form(\Varien_Data_Form_Element_Fieldset $fieldset)
    {
        $this->profile->getData()->willReturn([
            'this' => 'that'
        ]);
        $this->profile->getId()->willReturn(1);
        $this->profile->getProfileType()->willReturn('asdf');

        $this->form->getElement('base_fieldset')
            ->willReturn($fieldset);
        $prepareFormMethod = $this->refThis->getMethod('_prepareForm');
        $prepareFormMethod->setAccessible(true);
        $prepareFormMethod->invoke($this->getWrappedObject());
    }
}

class ugh {
    public function asArray() { return []; }
}