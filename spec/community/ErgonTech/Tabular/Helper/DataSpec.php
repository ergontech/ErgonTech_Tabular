<?php

namespace spec;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ErgonTech_Tabular_Helper_DataSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('ErgonTech_Tabular_Helper_Data');
    }
}
