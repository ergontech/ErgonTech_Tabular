<?php

namespace spec\ErgonTech\Tabular;

use ErgonTech\Tabular\Model_Profile;
use ErgonTech\Tabular\Model_Source_Profile_Type;
use Mage;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class Helper_RowTransformsSpec extends ObjectBehavior
{
    function it_transforms_widget_layout_rows(
        \Mage_Widget_Model_Widget_Instance $widget,
        \Mage_Widget_Model_Resource_Widget_Instance $widgetRes,
        \Mage_Core_Model_Config $config
    )
    {
        $refMage = new \ReflectionClass(\Mage::class);
        $refConfig = $refMage->getProperty('_config');
        $refConfig->setAccessible(true);
        $refConfig->setValue($refMage, $config->getWrappedObject());

        $config->getModelInstance('widget/widget_instance', Argument::type('array'))
            ->willReturn($widget);
        $widget->getResource()
            ->willReturn($widgetRes);
        $widgetRes->afterLoad(Argument::type(\Mage_Widget_Model_Widget_Instance::class))
            ->willReturn($widget);

        $widget->getData('page_groups')
            ->willReturn([[
                'page_id' => '1',
                'instance_id' => '1',
                'page_group' => 'all_pages',
                'layout_handle' => 'bar_foo',
                'block_reference' => 'foo.bar',
                'page_for' => 'all',
                'entities' => null,
                'page_template' => 'baz.phtml'
            ]]);
        $row = [
            'widget_id' => 'foo',
            'for' => 'specific',
            'page_group' => 'pages',
            'block' => 'foo.bar',
            'layout_handle' => 'foo_bar',
            'entities' => '123,345',
            'template' => 'foo/bar/baz.phtml',
            'stores' => '0'
        ];
        $newRow = $this->widgetLayoutRowTransform($row);

        $newRow->shouldBeArray();
        $otherPageGroup = $newRow['page_groups'][1];
        $otherPageGroup->shouldHaveKeyWithValue('page_group', 'pages');
        $otherPageGroup['pages']->shouldBeArray();
        $otherPageGroup['pages']->shouldHaveKeyWithValue('instance_id', 'foo');
        $otherPageGroup['pages']->shouldHaveKeyWithValue('for', 'specific');
        $otherPageGroup['pages']->shouldHaveKeyWithValue('page_group', 'pages');
        $otherPageGroup['pages']->shouldHaveKeyWithValue('block', 'foo.bar');
        $otherPageGroup['pages']->shouldHaveKeyWithValue('page_id', '0');
        $otherPageGroup['pages']->shouldHaveKeyWithValue('entities', '123,345');
        $otherPageGroup['pages']->shouldHaveKeyWithValue('layout_handle', 'foo_bar');
        $otherPageGroup['pages']->shouldHaveKeyWithValue('template', 'foo/bar/baz.phtml');

        $aPageGroup = $newRow['page_groups'][0];
        $aPageGroup->shouldHaveKeyWithValue('page_group', 'all_pages');
        $aPageGroup['all_pages']->shouldBeArray();
        $aPageGroup['all_pages']->shouldHaveKeyWithValue('instance_id', '1');
        $aPageGroup['all_pages']->shouldHaveKeyWithValue('for', 'all');
        $aPageGroup['all_pages']->shouldHaveKeyWithValue('page_group', 'all_pages');
        $aPageGroup['all_pages']->shouldHaveKeyWithValue('block', 'foo.bar');
        $aPageGroup['all_pages']->shouldHaveKeyWithValue('page_id', '1');
        $aPageGroup['all_pages']->shouldHaveKeyWithValue('entities', null);
        $aPageGroup['all_pages']->shouldHaveKeyWithValue('layout_handle', 'bar_foo');
        $aPageGroup['all_pages']->shouldHaveKeyWithValue('template', 'baz.phtml');
    }

    function it_gets_the_currently_configured_row_transform(
        \Mage_Core_Model_Config $config,
        Model_Profile $profile
    )
    {
        $profileType = 'asdf';
        $configRowTransform = 'blah';
        $rowTransformCallback = rowtransform::class . '::blah';

        $profile->getProfileType()
            ->willReturn($profileType)
            ->shouldBeCalled();

        $profile->getExtra('row_transform_callback')
            ->willReturn($configRowTransform);

        $refMage = new \ReflectionClass(Mage::class);
        $refConfig = $refMage->getProperty('_config');
        $refConfig->setAccessible(true);
        $refConfig->setValue($config->getWrappedObject());

        $config
            ->getNode(sprintf('%s/%s/extra/row_transform_callback/options/%s/callback',
                Model_Source_Profile_Type::CONFIG_PATH_PROFILE_TYPE,
                $profileType,
                $configRowTransform))
            ->willReturn($rowTransformCallback);

        $cb = $this->getRowTransformCallbackForProfile($profile);
        $cb->shouldHaveType(\Closure::class);
    }

    function it_can_collect_ids_for_entities_with_different_attributes(
        \Mage_Core_Model_Config $config,
        \Mage_Customer_Model_Customer $customer,
        \Mage_Catalog_Model_Category $category,
        Model_Profile $profile)
    {
        $refMage = new \ReflectionClass(Mage::class);
        $refConfig = $refMage->getProperty('_config');
        $refConfig->setAccessible(true);
        $refConfig->setValue($config->getWrappedObject());

        $config->getModelInstance('customer/customer', [])->willReturn($customer);
        $config->getModelInstance('catalog/category', [])->willReturn($category);

        $customer->loadByEmail('me@me.me')
            ->shouldBeCalled()
            ->willReturn($customer);
        $customer->getId()->willReturn(1);

        $category->loadByAttribute('name', 'CATS')
            ->shouldBeCalled()
            ->willReturn($category);
        $category->getId()->willReturn(1);

        $this->getEntityIdsFromColumn('customer/customer:loadByEmail:me@me.me', $profile);
        $this->getEntityIdsFromColumn('catalog/category:loadByAttribute:name:CATS', $profile);
    }
}

class rowtransform { public function blah() {} }
