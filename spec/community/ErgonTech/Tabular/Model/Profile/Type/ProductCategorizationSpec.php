<?php

namespace spec;

use ErgonTech\Tabular\GoogleSheetsLoadStep;
use ErgonTech\Tabular\HeaderTransformStep;
use ErgonTech\Tabular\IteratorStep;
use ErgonTech\Tabular\LoggingStep;
use ErgonTech\Tabular\Processor;
use ErgonTech\Tabular\Step\ProductCategorization\FastSimpleImport;
use Monolog\Logger;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ErgonTech_Tabular_Model_Profile_Type_ProductCategorizationSpec extends ObjectBehavior
{
    private $processor;

    private $api;
    private $storeGroupCollection;
    private $monologHelper;

    /**
     * @var \ErgonTech_Tabular_Helper_HeaderTransforms
     */
    private $headerTransforms;

    public function let(
        Processor $processor,
        \ErgonTech_Tabular_Helper_Google_Api $api,
        \ErgonTech_Tabular_Helper_HeaderTransforms $headerTransforms,
        \Google_Service_Sheets $sheets,
        Logger $logger,
        \ErgonTech_Tabular_Helper_Monolog $monologHelper,
        \Mage_Core_Model_Config $config,
        \Mage_Core_Model_Config_Options $configOptions,
        \Varien_Db_Select $select,
        \Mage_Catalog_Model_Resource_Category_Collection $categoryCollection,
        \Mage_Core_Model_Resource_Store_Group_Collection $storeGroupCollection,
        \AvS_FastSimpleImport_Model_Import $import
    ) {
        $this->processor = $processor;
        $this->api = $api;
        $this->headerTransforms = $headerTransforms;
        $this->monologHelper = $monologHelper;

        \Mage::app();

        $refMage = new \ReflectionClass(\Mage::class);
        $refConfig = $refMage->getProperty('_config');
        $refConfig->setAccessible(true);
        $refConfig->setValue($config->getWrappedObject());

        $config->getResourceModelInstance('core/store_group_collection', Argument::any())
            ->willReturn($storeGroupCollection);

        $config->getResourceModelInstance('catalog/category_collection', Argument::any())
            ->willReturn($categoryCollection);

        $config->getModelInstance('fastsimpleimport/import', Argument::any())->willReturn($import);

        $config->getOptions()->willReturn($configOptions);
        $configOptions->getDir('var')->willReturn('/tmp');

        $this->storeGroupCollection = $storeGroupCollection;
        $this->storeGroupCollection->getSelect()
            ->willReturn($select);

        $this->storeGroupCollection->join(Argument::type('array'), Argument::type('string'), Argument::any())
            ->willReturn($this->storeGroupCollection);

        $this->storeGroupCollection->getData()
            ->willReturn([]);

        $categoryCollection->addAttributeToSelect(Argument::type('string'))
            ->willReturn($categoryCollection);

        $categoryCollection->addIdFilter(Argument::type('array'))
            ->willReturn($categoryCollection);

        $categoryCollection->getColumnValues('name')
            ->willReturn(['root category name']);

        $this->headerTransforms
            ->getHeaderTransformCallbackForProfile(Argument::type(\ErgonTech_Tabular_Model_Profile::class))
            ->willReturn('strtolower');

        $this->api->getService(\Google_Service_Sheets::class, Argument::type('array'))
            ->willReturn($sheets);
        $this->monologHelper->registerLogger('tabular')->willReturn($logger);

        \Mage::register('_helper/ergontech_tabular/headerTransforms', $headerTransforms->getWrappedObject());
        \Mage::register('_helper/ergontech_tabular/google_api', $api->getWrappedObject());
        \Mage::register('_helper/ergontech_tabular/monolog', $this->monologHelper->getWrappedObject());
        $this->beConstructedWith($this->processor);
    }

    public function letGo()
    {
        \Mage::reset();
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(\ErgonTech_Tabular_Model_Profile_Type_ProductCategorization::class);
    }

    public function it_is_a_profile_type()
    {
        $this->shouldHaveType(\ErgonTech_Tabular_Model_Profile_Type::class);
    }

    public function it_can_only_be_initialized_once(\ErgonTech_Tabular_Model_Profile $profile)
    {
        $this->initialize($profile);
        $this->shouldThrow(\LogicException::class)->during('initialize', [$profile]);
    }

    public function it_must_be_initialized_before_executing()
    {
        $this->shouldThrow(\LogicException::class)->during('execute');
    }

    public function it_requires_a_header_transform_callback_before_running(\ErgonTech_Tabular_Model_Profile $profile)
    {
        $this->headerTransforms->getHeaderTransformCallbackForProfile($profile)
            ->willReturn(null);
        $this->initialize($profile);
        $this->shouldThrow(\Exception::class)->during('execute');
    }

    public function it_adds_the_right_steps_during_initialize(\ErgonTech_Tabular_Model_Profile $profile)
    {

        $this->processor->addStep(Argument::type(LoggingStep::class))->shouldBeCalledTimes(4);
        $this->processor->addStep(Argument::type(GoogleSheetsLoadStep::class))->shouldBeCalled();
        $this->processor->addStep(Argument::type(HeaderTransformStep::class))->shouldBeCalled();
        $this->processor->addStep(Argument::type(IteratorStep::class))->shouldBeCalled();
        $this->processor->addStep(Argument::type(FastSimpleImport::class))->shouldBeCalled();
        $this->initialize($profile);
    }

    public function it_runs_the_processor(\ErgonTech_Tabular_Model_Profile $profile)
    {
        $this->initialize($profile);
        $this->execute();
    }
}
