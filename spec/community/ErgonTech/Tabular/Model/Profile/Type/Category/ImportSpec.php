<?php

namespace spec;

use ErgonTech\Tabular\GoogleSheetsLoadStep;
use ErgonTech\Tabular\HeaderTransformStep;
use ErgonTech\Tabular\LoggingStep;
use ErgonTech\Tabular\Processor;
use ErgonTech\Tabular\Rows;
use ErgonTech\Tabular\Step\Category;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ErgonTech_Tabular_Model_Profile_Type_Category_ImportSpec extends ObjectBehavior
{
    /**
     * @var Processor
     */
    private $processor;

    public function let(
        Processor $processor,
        \Mage_Catalog_Model_Resource_Category_Collection $categoryCollection,
        \AvS_FastSimpleImport_Model_Import $import,
        \Mage_Core_Model_Config $config,
        \ErgonTech_Tabular_Helper_HeaderTransforms $headerTransforms
    ) {
        $this->processor = $processor;

        $this->beConstructedWith($this->processor);
        \Mage::app();
        $refMage = new \ReflectionClass(\Mage::class);
        $refConfig = $refMage->getProperty('_config');
        $refConfig->setAccessible(true);
        $refConfig->setValue($config->getWrappedObject());
        $config->getResourceModelInstance('catalog/category_collection', Argument::any())->willReturn($categoryCollection);
        $config->getModelInstance('fastsimpleimport/import', Argument::any())->willReturn($import);
        \Mage::register('_helper/ergontech_tabular/headerTransforms', $headerTransforms);
    }

    public function letGo()
    {
        \Mage::reset();
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(\ErgonTech_Tabular_Model_Profile_Type_Category_Import::class);
    }

    public function it_is_a_profile_type()
    {
        $this->shouldHaveType(\ErgonTech_Tabular_Model_Profile_Type::class);
    }


    public function it_can_only_be_initialized_once(\ErgonTech_Tabular_Model_Profile $profile, \ErgonTech_Tabular_Helper_Google_Api $api)
    {
        \Mage::register('_helper/ergontech_tabular/google_api', $api);
        $this->initialize($profile);
        $this->shouldThrow(\LogicException::class)->during('initialize', [$profile]);
    }

    public function it_requires_a_header_transform_callback_before_running(\ErgonTech_Tabular_Model_Profile $profile, \ErgonTech_Tabular_Helper_Google_Api $api)
    {
        \Mage::register('_helper/ergontech_tabular/google_api', $api);
        $this->initialize($profile);
        $this->shouldThrow(\LogicException::class)->during('execute');
    }

    public function it_adds_the_right_steps_to_the_Processor(
        \ErgonTech_Tabular_Model_Profile $profile,
        \ErgonTech_Tabular_Helper_Google_Api $api,
        \Google_Service_Sheets $sheetsService)
    {
        $api->getService(\Google_Service_Sheets::class)
            ->willReturn($sheetsService)
            /*->shouldBeCalled()*/;// what's up with this issue? :(
        \Mage::register('_helper/ergontech_tabular/google_api', $api);

        $this->processor->addStep(Argument::type(LoggingStep::class))->shouldBeCalledTimes(4);
        $this->processor->addStep(Argument::type(GoogleSheetsLoadStep::class))->shouldBeCalled();
        $this->processor->addStep(Argument::type(HeaderTransformStep::class))->shouldBeCalled();
        $this->processor->addStep(Argument::type(Category\FastSimpleImport::class))->shouldBeCalled();
        $this->processor->addStep(Argument::type(Category\RootCategoryCreator::class))->shouldBeCalled();
        $profile->getExtra('spreadsheet_id')->shouldBeCalled();
        $profile->getExtra('header_named_range')->shouldBeCalled();
        $profile->getExtra('data_named_range')->shouldBeCalled();

        $profile->getExtra('header_transform_callback')
            ->willReturn('strtolower');

        $this->initialize($profile);
    }

    public function it_runs_profile(\ErgonTech_Tabular_Model_Profile $profile, \ErgonTech_Tabular_Helper_Google_Api $api)
    {
        \Mage::register('_helper/ergontech_tabular/google_api', $api);
        $this->setHeaderTransformCallback(function ($a) {
        });
        $this->initialize($profile);
        $this->processor->run()->shouldBeCalled();

        $this->execute();
    }
}
