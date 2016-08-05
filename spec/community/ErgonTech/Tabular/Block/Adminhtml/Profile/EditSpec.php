<?php

namespace spec;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ErgonTech_Tabular_Block_Adminhtml_Profile_EditSpec extends ObjectBehavior
{
    function let(\ErgonTech_Tabular_Helper_Data $data)
    {
        \Mage::register('_helper/ergontech_tabular', $data);
        \Mage::register('_helper/ergontech_tabular/data', $data);
    }

    function letGo()
    {
        \Mage::unregister('_helper/ergontech_tabular');
        \Mage::unregister('_helper/ergontech_tabular/data');
        \Mage::reset();
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('ErgonTech_Tabular_Block_Adminhtml_Profile_Edit');
    }
}
