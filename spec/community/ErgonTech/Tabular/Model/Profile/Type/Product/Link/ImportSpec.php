<?php

namespace spec\ErgonTech\Tabular;

use ErgonTech\Tabular;
use ErgonTech\Tabular\Model_Profile;
use ErgonTech\Tabular\Processor;
use ErgonTech\Tabular\Step;
use Mage;
use Monolog\Logger;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class Model_Profile_Type_Product_Link_ImportSpec extends ObjectBehavior
{
    private $processor;
    private $profile;
    private $monologHelper;
    private $api;
    private $logger;
    private $headerTransforms;
    private $rowTransforms;

    /**
     * @var \Mage_Core_Model_Config
     */
    private $config;

    public function let(
        Processor $processor,
        \Mage_Catalog_Model_Resource_Product $productResource,
        \Mage_Catalog_Model_Resource_Product_link $productLinkResource,
        Tabular\Helper_HeaderTransforms $headerTransforms,
        Tabular\Helper_RowTransforms $rowTransforms,
        Logger $logger,
        Tabular\Helper_Monolog $monologHelper,
        \Google_Service_Sheets $sheetsService,
        Tabular\Helper_Google_Api $api,
        \Mage_Core_Model_Config $config,
        \Mage_Core_Model_Abstract $abstractModel,
        Model_Profile $profile,
        Step $step,
        \Mage_Core_Model_Config_Options $configOptions
    )
    {
        $this->processor = $processor;
        $this->api = $api;
        $this->headerTransforms = $headerTransforms;
        $this->rowTransforms = $rowTransforms;
        $this->monologHelper = $monologHelper;
        $this->logger = $logger;
        $this->profile = $profile;
        $this->config = $config;

        $api->getService(\Google_Service_Sheets::class, [\Google_Service_Sheets::SPREADSHEETS_READONLY])
            ->willReturn($sheetsService);
        $this->monologHelper->registerLogger(Argument::type('string'))->willReturn($logger);

        $this->beConstructedWith($processor);

        $refMage = new \ReflectionClass(Mage::class);
        $refConfig = $refMage->getProperty('_config');
        $refConfig->setAccessible(true);
        $refConfig->setValue($config->getWrappedObject());

        $processor->addStep(Argument::type(Step::class))->willReturn(null);

        $profileType = 'asdf';
        $classId = 'blah';

        $profile->getProfileType()
            ->willReturn($profileType);
        $profile->getName()
            ->willReturn('asdf');
        // Generic return value
        $profile->getExtra(Argument::type('string'))
            ->willReturn('asdf');
        $profile->getStores()->willReturn([1,2]);
        $profile->getExtra('link_type')
            ->willReturn(\Mage_Catalog_Model_Product_Link::LINK_TYPE_CROSSSELL);

        $config->getOptions()->willReturn($configOptions);
        $config->getModelInstance($classId, Argument::type('array'))->willReturn($abstractModel->getWrappedObject());
        $config->getModelInstance(Argument::type('string'), Argument::any())->willReturn($step->getWrappedObject());
        $config
            ->getNode(sprintf('%s/%s/entity',
                Tabular\Model_Source_Profile_Type::CONFIG_PATH_PROFILE_TYPE,
                $profileType))
            ->willReturn($classId);
        $config->getResourceModelInstance('catalog/product_link', Argument::type('array'))
            ->willReturn($productLinkResource);

        $configOptions->getDir('var')->willReturn('/tmp');

        $rowTransforms->getRowTransformCallbackForProfile(Argument::type(Model_Profile::class))
            ->willReturn('strtolower');
        $headerTransforms->getHeaderTransformCallbackForProfile(Argument::type(Model_Profile::class))
            ->willReturn('strtolower');

        Mage::register('_helper/ergontech_tabular/google_api', $api->getWrappedObject());
        Mage::register('_helper/ergontech_tabular/monolog', $this->monologHelper->getWrappedObject());
        Mage::register('_resource_singleton/catalog/product', $productResource->getWrappedObject());
        Mage::register('_helper/ergontech_tabular/headerTransforms', $headerTransforms->getWrappedObject());
        Mage::register('_helper/ergontech_tabular/rowTransforms', $rowTransforms->getWrappedObject());
    }
    function it_is_initializable()
    {
        $this->shouldHaveType(\ErgonTech\Tabular\Model_Profile_Type_Product_Link_Import::class);
    }

    function letGo()
    {
        Mage::reset();
    }

    function it_must_be_initialized_before_execute()
    {
        $this->shouldThrow(Tabular\Exception_Profile::class)->during('execute');
    }

    function it_can_only_be_initialized_once()
    {
        $exception = new Tabular\Exception_Profile('The profile must be intialized only once');
        $this->initialize($this->profile);
        $this->shouldThrow($exception)->during('initialize', [$this->profile]);
    }

    function it_creates_a_logger_during_initialize()
    {
        $this->monologHelper->registerLogger(Argument::type('string'))->shouldBeCalled();
        $this->initialize($this->profile);
    }

    function it_adds_the_right_steps_to_the_processor() {
        $this->processor->addStep(Argument::type(Tabular\LoggingStep::class))->shouldBeCalledTimes(5);
        $this->processor->addStep(Argument::type(Tabular\GoogleSheetsLoadStep::class))->shouldBeCalled();
        $this->processor->addStep(Argument::type(Tabular\HeaderTransformStep::class))->shouldBeCalled();
        $this->processor->addStep(Argument::type(Tabular\RowsTransformStep::class))->shouldBeCalled();
        $this->processor->addStep(Argument::type(Tabular\Step\ProductLinkSaveStep::class))->shouldBeCalled();

        $this->initialize($this->profile);
    }

}
