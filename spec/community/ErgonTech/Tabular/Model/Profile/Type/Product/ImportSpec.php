<?php

namespace spec\ErgonTech\Tabular;

use ErgonTech\Tabular;
use ErgonTech\Tabular\GoogleSheetsLoadStep;
use ErgonTech\Tabular\HeaderTransformStep;
use ErgonTech\Tabular\RowsTransformStep;
use ErgonTech\Tabular\LoggingStep;
use ErgonTech\Tabular\Model_Profile;
use ErgonTech\Tabular\Processor;
use ErgonTech\Tabular\Rows;
use ErgonTech\Tabular\Step\Product\FastSimpleImport;
use ErgonTech\Tabular\Step\EntityTransformStep;
use Monolog\Handler\HandlerInterface;
use Monolog\Logger;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class Model_Profile_Type_Product_ImportSpec extends ObjectBehavior
{
    private $headerTransforms;
    private $rowTransforms;
    private $api;
    private $monologHelper;
    private $logger;
    private $profile;
    /**
     * @var Processor
     */
    private $processor;

    public function let(Processor $processor,
                        \Mage_Catalog_Model_Resource_Product $productResource,
                        Tabular\Helper_HeaderTransforms $headerTransforms,
                        Tabular\Helper_RowTransforms $rowTransforms,
                        \Mage_Catalog_Model_Resource_Category_Collection $categoryCollection,
                        Logger $logger,
                        Tabular\Helper_Monolog $monologHelper,
                        \Google_Service_Sheets $sheetsService,
                        Tabular\Helper_Google_Api $api,
                        \Mage_Core_Model_Config $config,
                        \Mage_Core_Model_Config_Options $configOptions,
                        \AvS_FastSimpleImport_Model_Import $import,
                        Model_Profile $profile)
    {
        $this->profile = $profile;
        $this->processor = $processor;
        $this->api = $api;

        $profile->getProfileType()
            ->willReturn('asdf');

        $profile->getName()
            ->willReturn('asdf');

        $profile->getExtra(Argument::type('string'))
            ->willReturn('asdf');

        $this->api->getService(\Google_Service_Sheets::class, [\Google_Service_Sheets::SPREADSHEETS_READONLY])
            ->willReturn($sheetsService);
        \Mage::register('_helper/ergontech_tabular/google_api', $this->api->getWrappedObject());
        $this->monologHelper = $monologHelper;
        $this->logger = $logger;
        \Mage::register('_helper/ergontech_tabular/monolog', $this->monologHelper->getWrappedObject());
        $this->monologHelper->registerLogger(Argument::type('string'))->willReturn($logger);

        $this->beConstructedWith($this->processor);

        $refMage = new \ReflectionClass(\Mage::class);
        $refConfig = $refMage->getProperty('_config');
        $refConfig->setAccessible(true);
        $refConfig->setValue($config->getWrappedObject());
        $config->getResourceModelInstance('catalog/category_collection', Argument::any())->willReturn($categoryCollection);
        $config->getResourceModelInstance('catalog/product', Argument::type('array'))->willReturn($productResource);
        $config->getModelInstance('fastsimpleimport/import', Argument::any())->willReturn($import);
        $config->getOptions()->willReturn($configOptions);
        $configOptions->getDir('var')->willReturn('/tmp');

        $headerTransforms->getHeaderTransformCallbackForProfile(Argument::type(Model_Profile::class))
            ->willReturn('strtolower');
        $rowTransforms->getRowTransformCallbackForProfile(Argument::type(Model_Profile::class))
            ->willReturn('strtolower');

        \Mage::register('_resource_singleton/catalog/product', $productResource->getWrappedObject());
        \Mage::register('_helper/ergontech_tabular/headerTransforms', $headerTransforms->getWrappedObject());
        \Mage::register('_helper/ergontech_tabular/rowTransforms', $rowTransforms->getWrappedObject());
        $this->headerTransforms = $headerTransforms;
        $this->rowTransforms = $rowTransforms;
    }

    public function letGo()
    {
        \Mage::reset();
    }

    public function it_is_a_profile_type()
    {
        $this->shouldHaveType(Tabular\Model_Profile_Type::class);
    }

    public function it_can_only_be_initialized_once()
    {
        $this->initialize($this->profile);
        $this->shouldThrow(\LogicException::class)->during('initialize', [$this->profile]);
    }

    public function it_adds_the_right_steps_to_the_Processor()
    {
        $this->api->getService(\Google_Service_Sheets::class, [\Google_Service_Sheets::SPREADSHEETS_READONLY])
            ->shouldBeCalled();

        $this->processor->addStep(Argument::type(LoggingStep::class))->shouldBeCalledTimes(5);
        $this->processor->addStep(Argument::type(GoogleSheetsLoadStep::class))->shouldBeCalled();
        $this->processor->addStep(Argument::type(HeaderTransformStep::class))->shouldBeCalled();
        $this->processor->addStep(Argument::type(RowsTransformStep::class))->shouldBeCalled();
        $this->processor->addStep(Argument::type(EntityTransformStep::class))->shouldBeCalled();
        $this->processor->addStep(Argument::type(FastSimpleImport::class))->shouldBeCalled();

        $this->profile->getExtra('spreadsheet_id')->shouldBeCalled();
        $this->profile->getExtra('header_named_range')->shouldBeCalled();
        $this->profile->getExtra('data_named_range')->shouldBeCalled();
        $this->profile->getProfileType()
            ->willReturn('asdf')
            ->shouldBeCalled();

        $this->profile->getExtra('header_transform_callback')
            ->willReturn('strtolower');

        $this->initialize($this->profile);
    }

    public function it_adds_a_logger()
    {
        $this->monologHelper->registerLogger(Argument::type('string'))->shouldBeCalled();
        $this->logger->pushHandler(Argument::type(HandlerInterface::class))->shouldBeCalled();

        $this->initialize($this->profile);
    }

    public function it_runs_profile(Tabular\Helper_Google_Api $api)
    {
        $this->headerTransforms
            ->getHeaderTransformCallbackForProfile(Argument::type(Tabular\Model_Profile::class))
            ->willReturn('spec\ErgonTech\Tabular\ProductImportSpecTest::transform');
        $this->rowTransforms
            ->getRowTransformCallbackForProfile(Argument::type(Tabular\Model_Profile::class))
            ->willReturn('spec\ErgonTech\Tabular\ProductImportSpecTest::transform');

        $this->initialize($this->profile);
        $this->processor->run()->shouldBeCalled();

        $this->execute();
    }
}

class ProductImportSpecTest
{
    public static function transform($input) { return $input; }
}
