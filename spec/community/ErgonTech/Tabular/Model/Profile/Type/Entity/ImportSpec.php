<?php

namespace spec\ErgonTech\Tabular;

use ErgonTech\Tabular;
use ErgonTech\Tabular\Exception_Profile;
use ErgonTech\Tabular\Helper_HeaderTransforms;
use ErgonTech\Tabular\Model_Profile;
use ErgonTech\Tabular\Model_Profile_Type;
use ErgonTech\Tabular\Processor;
use ErgonTech\Tabular\Step;
use ErgonTech\Tabular\Step\EntitySaveStep;
use Mage;
use Monolog\Logger;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;

class Model_Profile_Type_Entity_ImportSpec extends ObjectBehavior
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
        $this->monologHelper->registerLogger('tabular')->willReturn($logger);

        $this->beConstructedWith($processor);
        Mage::app();

        $refMage = new \ReflectionClass(Mage::class);
        $refConfig = $refMage->getProperty('_config');
        $refConfig->setAccessible(true);
        $refConfig->setValue($config->getWrappedObject());

        $processor->addStep(Argument::type(Step::class))->willReturn(null);

        $profileType = 'asdf';
        $classId = 'blah';

        $profile->getProfileType()
            ->willReturn($profileType);
        // Generic return value
        $profile->getExtra(Argument::type('string'))
            ->willReturn('asdf');
        $profile->getStores()->willReturn([1,2]);

        $config->getOptions()->willReturn($configOptions);
        $config->getModelInstance($classId, Argument::type('array'))->willReturn($abstractModel->getWrappedObject());
        $config->getModelInstance(Argument::type('string'), Argument::any())->willReturn($step->getWrappedObject());
        $config
            ->getNode(sprintf('%s/%s/entity',
                Tabular\Model_Source_Profile_Type::CONFIG_PATH_PROFILE_TYPE,
                $profileType))
            ->willReturn($classId);

        $configOptions->getDir('var')->willReturn('/tmp');

        Mage::register('_helper/ergontech_tabular/google_api', $api->getWrappedObject());
        Mage::register('_helper/ergontech_tabular/monolog', $this->monologHelper->getWrappedObject());
        Mage::register('_resource_singleton/catalog/product', $productResource->getWrappedObject());
        Mage::register('_helper/ergontech_tabular/headerTransforms', $headerTransforms->getWrappedObject());
        Mage::register('_helper/ergontech_tabular/rowTransforms', $rowTransforms->getWrappedObject());
    }

    function letGo()
    {
        Mage::reset();
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(\ErgonTech\Tabular\Model_Profile_Type_Entity_Import::class);
    }

    function it_is_a_profile_type()
    {
        $this->shouldHaveType(Model_Profile_Type::class);
    }

    function it_must_be_initialized_before_execute()
    {
        $this->shouldThrow(Exception_Profile::class)->during('execute');
    }

    function it_can_only_be_initialized_once()
    {
        $exception = new Exception_Profile('Can only initialize the profile one time');
        $this->initialize($this->profile);
        $this->shouldThrow($exception)->during('initialize', [$this->profile]);
    }

    function it_creates_a_logger_during_initialize()
    {
        $this->monologHelper->registerLogger('tabular')->shouldBeCalled();
        $this->initialize($this->profile);
    }

    function it_adds_the_right_steps_to_the_processor(
        EntitySaveStep $entitySaveStep,
        Tabular\LoggingStep $loggingStep
    ) {
        $this->processor->addStep(Argument::type(Tabular\LoggingStep::class))->shouldBeCalledTimes(5);
        $this->processor->addStep(Argument::type(Tabular\GoogleSheetsLoadStep::class))->shouldBeCalled();
        $this->processor->addStep(Argument::type(Tabular\HeaderTransformStep::class))->shouldBeCalled();
        $this->processor->addStep(Argument::type(Tabular\RowsTransformStep::class))->shouldBeCalled();
        $this->processor->addStep(Argument::type(Tabular\IteratorStep::class))->shouldBeCalled();
        $this->processor->addStep(Argument::type(EntitySaveStep::class))->shouldBeCalled();

        $this->initialize($this->profile);
    }

    function it_reads_extra_data_from_the_profile()
    {
        $this->profile->getExtra('spreadsheet_id')
            ->willReturn('asdf')
            ->shouldBeCalled();
        $this->profile->getExtra('header_named_range')
            ->willReturn('asdf')
            ->shouldBeCalled();
        $this->profile->getExtra('data_named_range')
            ->willReturn('asdf')
            ->shouldBeCalled();

        $this->initialize($this->profile);
    }

    function it_initializes_the_google_api(
        \Google_Service_Sheets $sheets
    ) {
        $this->api->getService(\Google_Service_Sheets::class, [\Google_Service_Sheets::SPREADSHEETS_READONLY])
            ->willReturn($sheets)
            ->shouldBeCalled();

        $this->initialize($this->profile);
    }

    function it_runs_the_processor()
    {
        $this->processor->run()->shouldBeCalled();
        $this->initialize($this->profile);
        $this->execute();
    }


}
