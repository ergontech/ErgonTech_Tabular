<?php

namespace spec\ErgonTech\Tabular;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class Helper_DataSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(\ErgonTech\Tabular\Helper_Data::class);
    }
}
