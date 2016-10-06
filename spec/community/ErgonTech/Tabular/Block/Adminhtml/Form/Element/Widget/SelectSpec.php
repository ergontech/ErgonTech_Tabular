<?php

namespace spec\ErgonTech\Tabular;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class Block_Adminhtml_Form_Element_Widget_SelectSpec extends ObjectBehavior
{
    function it_is_a_select()
    {
        $this->shouldHaveType(\Varien_Data_Form_Element_Select::class);
    }
}
