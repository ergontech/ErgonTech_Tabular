<?php

namespace spec\ErgonTech\Tabular;

use Mage;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use ErgonTech\Tabular;

class Helper_Google_ApiSpec extends ObjectBehavior
{
    private $app;
    protected $store;

    /**
     * @var \Google_Client
     */
    protected $client;

    protected $dataHelper;

    function let(
        \Mage_Core_Model_Config $config,
        \Mage_Core_Model_App $app,
        \Mage_Core_Model_Store $store,
        \Google_Client $client,
        \Mage_Core_Helper_Data $dataHelper
    ) {

        $refMage = new \ReflectionClass(Mage::class);

        // Set store on mage
        $refApp = $refMage->getProperty('_app');
        $refApp->setAccessible(true);
        $refApp->setValue($refMage, $app->getWrappedObject());

        $app->getStore(Argument::any())->willReturn($store);

        $this->store = $store;
        $this->client = $client;
        $this->dataHelper = $dataHelper;
        Mage::register('_singleton/Google_Client', $this->client->getWrappedObject());
        Mage::register('_helper/core', $this->dataHelper->getWrappedObject());
    }

    function letGo()
    {
        Mage::reset();
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(Tabular\Helper_Google_Api::class);
    }

    function it_can_get_a_service()
    {
        $scopes = [\Google_Service_Sheets::SPREADSHEETS_READONLY];
        $sheetsClassname = \Google_Service_Sheets::class;

        $this->client->setScopes($scopes)
            ->shouldBeCalled();
        $this->client->setAuthConfig(Argument::any())
            ->shouldBeCalled();

        $this->getService($sheetsClassname, $scopes)
            ->shouldReturnAnInstanceOf($sheetsClassname);
    }

    function it_can_get_a_Google_Client()
    {

        $this->getGoogleClient()->shouldReturnAnInstanceOf(\Google_Client::class);
    }

    function it_reads_api_key_from_system_config(\Mage_Core_Model_Store $store)
    {
        $apiKey = 'apikey!!';
        $this->store->getConfig(Tabular\Helper_Google_Api::CONFIG_PATH_API_TYPE)
            ->willReturn(Tabular\Model_Source_Google_Api_Type::API_KEY)
            ->shouldBeCalled();
        $this->store->getConfig(Tabular\Helper_Google_Api::CONFIG_PATH_API_KEY)
            ->willReturn($apiKey)
            ->shouldBeCalled();
        $this->client->setDeveloperKey($apiKey)
            ->shouldBeCalled();
        $this->getGoogleClient();
    }

    function it_reads_json_config_when_the_type_is_service_account()
    {
        $authConfig = ['hello' => 'world'];
        $authConfigStr = json_encode($authConfig);
        $this->dataHelper->jsonDecode($authConfigStr)->willReturn($authConfig);

        // We should check which api type is to be used
        $this->store->getConfig(Tabular\Helper_Google_Api::CONFIG_PATH_API_TYPE)
            ->willReturn(Tabular\Model_Source_Google_Api_Type::SERVICE_ACCOUNT)
            ->shouldBeCalled();

        // We should ask for the "auth data"
        $this->store->getConfig(Tabular\Helper_Google_Api::CONFIG_PATH_API_KEY)
            ->willReturn($authConfigStr)
            ->shouldBeCalled();

        // We should set the client's config based on what was found
        $this->client->setAuthConfig($authConfig)
            ->shouldBeCalled();

        $this->getGoogleClient();
    }
}
