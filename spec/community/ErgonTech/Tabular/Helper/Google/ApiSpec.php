<?php

namespace spec;

use Mage;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ErgonTech_Tabular_Helper_Google_ApiSpec extends ObjectBehavior
{
    private $app;
    function let()
    {
        $this->app = Mage::app();
    }

    function letGo()
    {
        Mage::reset();
    }

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

    function it_reads_api_key_from_system_config(\Mage_Core_Model_Store $store)
    {
        $isInstalled = new \ReflectionProperty($this->app, '_isInstalled');
        $isInstalled->setAccessible(true);
        $isInstalled->setValue($this->app, true);
        $this->app->setCurrentStore($store->getWrappedObject());
        $store->getConfig(\ErgonTech_Tabular_Helper_Google_Api::CONFIG_PATH_API_KEY)
            ->willReturn('key')
            ->shouldBeCalled();

        $this->getGoogleClient();
    }
}
