<?php

namespace spec\ErgonTech\Tabular;

use ErgonTech\Tabular\Exception_Profile;
use ErgonTech\Tabular\GoogleSheetsLoadStep;
use ErgonTech\Tabular\HeaderTransformStep;
use ErgonTech\Tabular\Helper_Google_Api;
use ErgonTech\Tabular\Helper_HeaderTransforms;
use ErgonTech\Tabular\Helper_Monolog;
use ErgonTech\Tabular\IteratorStep;
use ErgonTech\Tabular\LoggingStep;
use ErgonTech\Tabular\Model_Profile;
use ErgonTech\Tabular\Model_Profile_Type;
use ErgonTech\Tabular\Processor;
use ErgonTech\Tabular\Rows;
use ErgonTech\Tabular\Step\Category;
use Monolog\Logger;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class Model_Profile_Type_Category_ImportSpec extends ObjectBehavior
{
    /**
     * @var Processor
     */
    private $processor;

    public function let(
        Processor $processor,
        \Mage_Catalog_Model_Resource_Category_Collection $categoryCollection,
        \Mage_Core_Model_Resource_Store_Group_Collection $storeGroupCollection,
        \Varien_Db_Select $select,
        \AvS_FastSimpleImport_Model_Import $import,
        \Mage_Core_Model_Config $config,
        \Mage_Core_Model_Config_Options $configOptions,
        Helper_HeaderTransforms $headerTransforms,
        Helper_Google_Api $api,
        Helper_Monolog $monologHelper,
        \Google_Service_Sheets $sheetsService,
        Logger $logger
    )
    {
        $this->processor = $processor;

        $this->beConstructedWith($this->processor);

        $refMage = new \ReflectionClass(\Mage::class);
        $refConfig = $refMage->getProperty('_config');
        $refConfig->setAccessible(true);
        $refConfig->setValue($config->getWrappedObject());

        $config->getResourceModelInstance('catalog/category_collection', Argument::type('array'))
            ->willReturn($categoryCollection);
        $categoryCollection->addAttributeToSelect(Argument::type('string'))
            ->willReturn($categoryCollection);
        $categoryCollection->addRootLevelFilter()
            ->willReturn($categoryCollection);
        $categoryCollection->addIdFilter(Argument::type('array'))
            ->willReturn($categoryCollection);
        $categoryCollection->getColumnValues('name')
            ->willReturn(['a', 'b']);

        $config->getModelInstance('fastsimpleimport/import', Argument::type('array'))
            ->willReturn($import);

        $config->getResourceModelInstance('core/store_group_collection', Argument::type('array'))
            ->willReturn($storeGroupCollection);
        $storeGroupCollection->join(Argument::type('array'), Argument::type('string'), Argument::any())
            ->willReturn($storeGroupCollection);
        $storeGroupCollection->getData()
            ->willReturn([
                ['root_category_id' => 2],
                ['root_category_id' => 3],
            ]);
        $storeGroupCollection->getSelect()
            ->willReturn($select);
        $select->where(Argument::type('string'), Argument::any())
            ->willReturn(null);

        $config->getOptions()->willReturn($configOptions);
        $configOptions->getDir('var')->willReturn('/tmp');

        \Mage::register('_helper/ergontech_tabular/headerTransforms', $headerTransforms->getWrappedObject());
        \Mage::register('_helper/ergontech_tabular/monolog', $monologHelper->getWrappedObject());
        \Mage::register('_helper/ergontech_tabular/google_api', $api->getWrappedObject());

        $transform = new catimportblah();
        /** @var \ReflectionClass $ref */
        $ref = new \ReflectionClass($transform);
        /** @var \ReflectionMethod $met */
        $met = $ref->getMethod('trnsfrm');
        $headerTransforms->getHeaderTransformCallbackForProfile(Argument::type(Model_Profile::class))
            ->willReturn($met->getClosure($transform));


        $api->getService(\Google_Service_Sheets::class, [\Google_Service_Sheets::SPREADSHEETS_READONLY])
            ->willReturn($sheetsService);

        $monologHelper->getLogger(Argument::type('string'))->willReturn($logger);
        $monologHelper->registerLogger(Argument::type('string'))->willReturn($logger);
    }

    public function letGo()
    {
        \Mage::reset();
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(\ErgonTech\Tabular\Model_Profile_Type_Category_Import::class);
    }

    public function it_is_a_profile_type()
    {
        $this->shouldHaveType(Model_Profile_Type::class);
    }

    public function it_can_only_be_initialized_once(Model_Profile $profile)
    {
        $this->initialize($profile);
        $this->shouldThrow(Exception_Profile::class)->during('initialize', [$profile]);
    }

    public function it_adds_the_right_steps_to_the_Processor(Model_Profile $profile)
    {
        $this->processor->addStep(Argument::type(LoggingStep::class))->shouldBeCalledTimes(4);
        $this->processor->addStep(Argument::type(GoogleSheetsLoadStep::class))->shouldBeCalled();
        $this->processor->addStep(Argument::type(HeaderTransformStep::class))->shouldBeCalled();
        $this->processor->addStep(Argument::type(Category\FastSimpleImport::class))->shouldBeCalled();
        $this->processor->addStep(Argument::type(IteratorStep::class))->shouldBeCalled();

        $profile->getExtra('spreadsheet_id')->shouldBeCalled();
        $profile->getExtra('header_named_range')->shouldBeCalled();
        $profile->getExtra('data_named_range')->shouldBeCalled();
        $profile->getProfileType()->willReturn('blah')->shouldBeCalled();
        $profile->getStores()->willReturn([1,2,3]);

        $profile->getExtra('header_transform_callback')
            ->willReturn('strtolower');

        $this->initialize($profile);
    }

    public function it_runs_profile(Model_Profile $profile)
    {
        $this->initialize($profile);
        $this->processor->run()->shouldBeCalled();

        $this->execute();
    }
}

class catimportblah
{
    public function trnsfrm($x)
    {
        return $x;
    }
}