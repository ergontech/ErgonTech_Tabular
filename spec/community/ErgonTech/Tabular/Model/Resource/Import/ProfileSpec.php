<?php

namespace spec;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ErgonTech_Tabular_Model_Resource_Import_ProfileSpec extends ObjectBehavior
{
    public function let()
    {
        \Mage::app();
    }

    public function letGo()
    {
        \Mage::reset();
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(\ErgonTech_Tabular_Model_Resource_Import_Profile::class);
    }
}
