<?php

namespace spec\ErgonTech\Tabular;

use ErgonTech\Tabular;
use ErgonTech\Tabular\GoogleSheetsLoadStep;
use ErgonTech\Tabular\HeaderTransformStep;
use ErgonTech\Tabular\IteratorStep;
use ErgonTech\Tabular\LoggingStep;
use ErgonTech\Tabular\Processor;
use ErgonTech\Tabular\Step\ProductCategorization\FastSimpleImport;
use Monolog\Logger;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class Model_Profile_Type_ProductCategorizationSpec extends ObjectBehavior
{
    private $processor;

    private $api;
    private $storeGroupCollection;
    private $monologHelper;

    /**
     * @var Tabular\Helper_HeaderTransforms
     */
    private $headerTransforms;
    private $profile;

    public function let(
        Processor $processor,
        Tabular\Helper_Google_Api $api,
        Tabular\Helper_HeaderTransforms $headerTransforms,
        \Google_Service_Sheets $sheets,
        Logger $logger,
        Tabular\Helper_Monolog $monologHelper,
        \Mage_Core_Model_Config $config,
        \Mage_Core_Model_Config_Options $configOptions,
        \Varien_Db_Select $select,
        \Mage_Catalog_Model_Resource_Category_Collection $categoryCollection,
        \Mage_Core_Model_Resource_Store_Group_Collection $storeGroupCollection,
        \AvS_FastSimpleImport_Model_Import $import,
        Tabular\Model_Profile $profile
    )
    {

        $this->processor = $processor;
        $this->profile = $profile;
        $this->api = $api;
        $this->headerTransforms = $headerTransforms;
        $this->monologHelper = $monologHelper;

        $profile->getProfileType()
            ->willReturn('asdf');
        $profile->getName()
            ->willReturn('asdf');
        // Generic return value
        $profile->getExtra(Argument::type('string'))
            ->willReturn('asdf');
        $profile->getStores()->willReturn([1,2]);

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
            ->getHeaderTransformCallbackForProfile(Argument::type(Tabular\Model_Profile::class))
            ->willReturn('strtolower');

        $this->api->getService(\Google_Service_Sheets::class, Argument::type('array'))
            ->willReturn($sheets);
        $this->monologHelper->registerLogger(Argument::type('string'))->willReturn($logger);

        \Mage::register('_helper/ergontech_tabular/headerTransforms', $headerTransforms->getWrappedObject());
        \Mage::register('_helper/ergontech_tabular/google_api', $api->getWrappedObject());
        \Mage::register('_helper/ergontech_tabular/monolog', $this->monologHelper->getWrappedObject());

        $this->beConstructedWith($this->processor);
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

    public function it_must_be_initialized_before_executing()
    {
        $this->shouldThrow(\LogicException::class)->during('execute');
    }

    public function it_adds_the_right_steps_during_initialize()
    {

        $this->processor->addStep(Argument::type(LoggingStep::class))->shouldBeCalledTimes(4);
        $this->processor->addStep(Argument::type(GoogleSheetsLoadStep::class))->shouldBeCalled();
        $this->processor->addStep(Argument::type(HeaderTransformStep::class))->shouldBeCalled();
        $this->processor->addStep(Argument::type(Tabular\Step\ProfileStoresToRootCategoriesIterator::class))->shouldBeCalled();
        $this->processor->addStep(Argument::type(FastSimpleImport::class))->shouldBeCalled();
        $this->initialize($this->profile);
    }

    public function it_runs_the_processor()
    {
        $this->initialize($this->profile);
        $this->execute();
    }
}
