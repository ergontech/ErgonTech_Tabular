<?php

namespace spec\ErgonTech\Tabular;

use ErgonTech\Tabular;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class Block_Adminhtml_Profile_PageSpec extends ObjectBehavior
{
    function let(Tabular\Helper_Data $data)
    {
        \Mage::register('_helper/ergontech_tabular', $data);
    }

    function letGo()
    {
        \Mage::reset();
    }
    
    function it_is_initializable()
    {
        $this->shouldHaveType(Tabular\Block_Adminhtml_Profile_Page::class);
    }
}
