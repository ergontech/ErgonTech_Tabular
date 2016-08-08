<?php

namespace spec;

use ErgonTech_Tabular_Block_Adminhtml_Profile_Grid;
use ErgonTech_Tabular_Helper_Data;
use Mage_Adminhtml_Block_Widget_Grid;
use Mage_Adminhtml_Block_Widget_Grid_Column;
use PhpParser\Node\Arg;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ErgonTech_Tabular_Block_Adminhtml_Profile_GridSpec extends ObjectBehavior
{
    function let(\Mage_Admin_Helper_Data $helper)
    {
        \Mage::register('_helper/adminhtml', $helper->getWrappedObject());
    }

    function letGo()
    {
        \Mage::reset();
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ErgonTech_Tabular_Block_Adminhtml_Profile_Grid::class);
    }

    function it_is_an_adminhtml_grid()
    {
        $this->shouldHaveType(Mage_Adminhtml_Block_Widget_Grid::class);
    }

    function it_adds_columns_to_itself(
        \ArrayObject $columns,
        ErgonTech_Tabular_Helper_Data $data,
        \Mage_Core_Model_Layout $layout,
        \Mage_Core_Block_Template $template,
        Mage_Adminhtml_Block_Widget_Grid_Column $column
    ) {
        \Mage::register('_helper/ergontech_tabular', $data->getWrappedObject());
        $sortaThis = $this->getWrappedObject();
        $reflThis = new \ReflectionClass($sortaThis);
        $reflColumns = $reflThis->getProperty('_columns');
        $reflColumns->setAccessible(true);
        $reflColumns->setValue($sortaThis, $columns->getWrappedObject());

        /** @var \ReflectionMethod $reflPrepareColumns */
        $reflPrepareColumns = $reflThis->getMethod('_prepareColumns');
        $reflPrepareColumns->setAccessible(true);

        $reflLayout = $reflThis->getProperty('_layout');
        $reflLayout->setAccessible(true);
        $reflLayout->setValue($sortaThis, $layout->getWrappedObject());
        $layout->createBlock(Argument::type('string'), Argument::type('string'), Argument::type('array'))
            ->willReturn(clone $template);

        $layout->createBlock('adminhtml/widget_grid_column')
            ->willReturn(clone $column);

        $column->setData(Argument::any(), Argument::any())->willReturn($column);
        $column->setGrid(Argument::any())->willReturn($column);
        $column->setId(Argument::type('string'))->willReturn($column);

        $columns->offsetSet('name', Argument::type(\Mage_Core_Block_Template::class))
            ->shouldBeCalled();
        $columns->offsetGet('name')->willReturn($column);

        $columns->offsetSet('profile_type', Argument::type(\Mage_Core_Block_Template::class))
            ->shouldBeCalled();
        $columns->offsetGet('profile_type')->willReturn($column);

        // This is only true as long as we're in multi-store mode...
        $columns->offsetSet('store_id', Argument::type(\Mage_Core_Block_Template::class))
            ->shouldBeCalled();
        $columns->offsetGet('store_id')->willReturn($column);

        $reflPrepareColumns->invoke($sortaThis);
    }
}
