<?php

namespace spec\ErgonTech\Tabular;

use ErgonTech\Tabular;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class Block_Adminhtml_Profile_EditSpec extends ObjectBehavior
{
    function let(Tabular\Helper_Data $tabularHelper, \Mage_Adminhtml_Helper_Data $adminhtmlHelper)
    {
        \Mage::register('_helper/ergontech_tabular', $tabularHelper->getWrappedObject());
        \Mage::register('_helper/adminhtml', $adminhtmlHelper->getWrappedObject());
        \Mage::register('_helper/ergontech_tabular/data', $tabularHelper->getWrappedObject());
        \Mage::register('_helper/adminhtml/data', $adminhtmlHelper->getWrappedObject());
    }

    function letGo()
    {
        \Mage::unregister('_helper/ergontech_tabular');
        \Mage::unregister('_helper/ergontech_tabular/data');
        \Mage::reset();
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(Tabular\Block_Adminhtml_Profile_Edit::class);
    }
}
