<?php

namespace spec;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ErgonTech_Tabular_Helper_Google_ApiSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('ErgonTech_Tabular_Helper_Google_Api');
    }

    function it_can_get_a_service()
    {
        $sheetsClassname = \Google_Service_Sheets::class;
        $this->getService($sheetsClassname)->shouldReturnAnInstanceOf($sheetsClassname);
    }

    function it_can_get_a_Google_Client()
    {
        $this->getGoogleClient()->shouldReturnAnInstanceOf(\Google_Client::class);
    }
}
