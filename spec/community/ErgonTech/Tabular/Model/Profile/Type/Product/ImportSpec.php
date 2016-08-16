<?php

namespace spec\ErgonTech\Tabular;

use ErgonTech\Tabular;
use ErgonTech\Tabular\GoogleSheetsLoadStep;
use ErgonTech\Tabular\HeaderTransformStep;
use ErgonTech\Tabular\LoggingStep;
use ErgonTech\Tabular\Processor;
use ErgonTech\Tabular\Rows;
use ErgonTech\Tabular\Step\Product\FastSimpleImport;
use Monolog\Handler\HandlerInterface;
use Monolog\Logger;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class Model_Profile_Type_Product_ImportSpec extends ObjectBehavior
{
    private $headerTransforms;
    private $api;
    private $monologHelper;
    private $logger;
    /**
     * @var Processor
     */
    private $processor;

    public function let(Processor $processor,
                        \Mage_Catalog_Model_Resource_Product $productResource,
                        Tabular\Helper_HeaderTransforms $headerTransforms,
                        \Mage_Catalog_Model_Resource_Category_Collection $categoryCollection,
                        Logger $logger,
                        Tabular\Helper_Monolog $monologHelper,
                        \Google_Service_Sheets $sheetsService,
                        Tabular\Helper_Google_Api $api,
                        \Mage_Core_Model_Config $config,
                        \Mage_Core_Model_Config_Options $configOptions,
                        \AvS_FastSimpleImport_Model_Import $import)
    {
        $this->processor = $processor;
        $this->api = $api;

        $this->api->getService(\Google_Service_Sheets::class, [\Google_Service_Sheets::SPREADSHEETS_READONLY])
            ->willReturn($sheetsService);
        \Mage::register('_helper/ergontech_tabular/google_api', $this->api->getWrappedObject());
        $this->monologHelper = $monologHelper;
        $this->logger = $logger;
        \Mage::register('_helper/ergontech_tabular/monolog', $this->monologHelper->getWrappedObject());
        $this->monologHelper->registerLogger('tabular')->willReturn($logger);

        $this->beConstructedWith($this->processor);
        \Mage::app();

        $refMage = new \ReflectionClass(\Mage::class);
        $refConfig = $refMage->getProperty('_config');
        $refConfig->setAccessible(true);
        $refConfig->setValue($config->getWrappedObject());
        $config->getResourceModelInstance('catalog/category_collection', Argument::any())->willReturn($categoryCollection);
        $config->getModelInstance('fastsimpleimport/import', Argument::any())->willReturn($import);
        $config->getOptions()->willReturn($configOptions);
        $configOptions->getDir('var')->willReturn('/tmp');

        \Mage::register('_resource_singleton/catalog/product', $productResource->getWrappedObject());
        \Mage::register('_helper/ergontech_tabular/headerTransforms', $headerTransforms->getWrappedObject());
        $this->headerTransforms = $headerTransforms;
    }

    public function letGo()
    {
        \Mage::reset();
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(Tabular\Model_Profile_Type_Product_Import::class);
    }

    public function it_is_a_profile_type()
    {
        $this->shouldHaveType(Tabular\Model_Profile_Type::class);
    }

    public function it_can_only_be_initialized_once(Tabular\Model_Profile $profile)
    {
        $this->initialize($profile);
        $this->shouldThrow(\LogicException::class)->during('initialize', [$profile]);
    }

    public function it_requires_a_header_transform_callback_before_running(Tabular\Model_Profile $profile)
    {
        $this->initialize($profile);
        $this->shouldThrow(\LogicException::class)->during('execute');
    }

    public function it_adds_the_right_steps_to_the_Processor(Tabular\Model_Profile $profile)
    {
        $this->api->getService(\Google_Service_Sheets::class, [\Google_Service_Sheets::SPREADSHEETS_READONLY])
            ->shouldBeCalled();

        $this->processor->addStep(Argument::type(LoggingStep::class))->shouldBeCalledTimes(3);
        $this->processor->addStep(Argument::type(GoogleSheetsLoadStep::class))->shouldBeCalled();
        $this->processor->addStep(Argument::type(HeaderTransformStep::class))->shouldBeCalled();
        $this->processor->addStep(Argument::type(FastSimpleImport::class))->shouldBeCalled();

        $profile->getExtra('spreadsheet_id')->shouldBeCalled();
        $profile->getExtra('header_named_range')->shouldBeCalled();
        $profile->getExtra('data_named_range')->shouldBeCalled();
        $profile->getProfileType()->shouldBeCalled();

        $profile->getExtra('header_transform_callback')
            ->willReturn('strtolower');

        $this->initialize($profile);
    }

    public function it_adds_a_logger(Tabular\Model_Profile $profile)
    {
        $this->monologHelper->registerLogger('tabular')->shouldBeCalled();
        $this->logger->pushHandler(Argument::type(HandlerInterface::class))->shouldBeCalled();

        $this->initialize($profile);
    }

    public function it_runs_profile(Tabular\Model_Profile $profile, Tabular\Helper_Google_Api $api)
    {
        $this->headerTransforms
            ->getHeaderTransformCallbackForProfile(Argument::type(Tabular\Model_Profile::class))
            ->willReturn('spec\ErgonTech\Tabular\ProductImportSpecTest::transform');

        $this->initialize($profile);
        $this->processor->run()->shouldBeCalled();

        $this->execute();
    }
}

class ProductImportSpecTest
{
    public static function transform($input) { return $input; }
}
