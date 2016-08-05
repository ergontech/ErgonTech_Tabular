<?php

namespace spec;

use Mage;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ErgonTech_Tabular_Helper_Google_ApiSpec extends ObjectBehavior
{
    private $app;
    protected $store;

    protected $client;

    function let(
        \Mage_Core_Model_Config $config,
        \Mage_Core_Model_App $app,
        \Mage_Core_Model_Store $store,
        \Google_Client $client
    ) {

        $refMage = new \ReflectionClass(Mage::class);

        // Set store on mage
        $refApp = $refMage->getProperty('_app');
        $refApp->setAccessible(true);
        $refApp->setValue($refMage, $app->getWrappedObject());

        $app->getStore(Argument::any())->willReturn($store);

        $this->store = $store;
        $this->client = $client;
        Mage::register('_singleton/Google_Client', $this->client->getWrappedObject());
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
        $apiKey = 'apikey!!';
        $this->store->getConfig(\ErgonTech_Tabular_Helper_Google_Api::CONFIG_PATH_API_TYPE)
            ->willReturn(\ErgonTech_Tabular_Model_Source_Google_Api_Type::API_KEY)
            ->shouldBeCalled();
        $this->store->getConfig(\ErgonTech_Tabular_Helper_Google_Api::CONFIG_PATH_API_KEY)
            ->willReturn($apiKey)
            ->shouldBeCalled();
        $this->client->setDeveloperKey($apiKey)
            ->shouldBeCalled();
        $this->getGoogleClient();
    }

    function it_reads_json_config_when_the_type_is_service_account()
    {
        $authConfig = ['hello' => 'world'];
        $this->store->getConfig(\ErgonTech_Tabular_Helper_Google_Api::CONFIG_PATH_API_TYPE)
            ->willReturn(\ErgonTech_Tabular_Model_Source_Google_Api_Type::SERVICE_ACCOUNT)
            ->shouldBeCalled();
        $this->store->getConfig(\ErgonTech_Tabular_Helper_Google_Api::CONFIG_PATH_API_KEY)
            ->willReturn($authConfig)
            ->shouldBeCalled();
        $this->client->setAuthConfig($authConfig)
            ->shouldBeCalled();
        $this->getGoogleClient();
    }
}
