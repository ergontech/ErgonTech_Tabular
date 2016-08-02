<?php

namespace spec;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ErgonTech_Tabular_Block_Adminhtml_Profile_PageSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('ErgonTech_Tabular_Block_Adminhtml_Profile_Page');
    }
}
