<?php

namespace spec;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ErgonTech_Tabular_Block_Adminhtml_Profile_PageSpec extends ObjectBehavior
{
    function let(\ErgonTech_Tabular_Helper_Data $data)
    {
        \Mage::register('_helper/ergontech_tabular', $data);
    }

    function letGo()
    {
        \Mage::reset();
    }
    
    function it_is_initializable()
    {
        $this->shouldHaveType('ErgonTech_Tabular_Block_Adminhtml_Profile_Page');
    }
}
