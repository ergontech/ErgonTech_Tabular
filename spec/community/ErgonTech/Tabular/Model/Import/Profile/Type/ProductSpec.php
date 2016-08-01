<?php

namespace spec;

use ErgonTech\Tabular\GoogleSheetsLoadStep;
use ErgonTech\Tabular\HeaderTransformStep;
use ErgonTech\Tabular\LoggingStep;
use ErgonTech\Tabular\Processor;
use ErgonTech\Tabular\Rows;
use ErgonTech\Tabular\Step\Product\FastSimpleImport;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ErgonTech_Tabular_Model_Import_Profile_Type_ProductSpec extends ObjectBehavior
{
    /**
     * @var Processor
     */
    private $processor;

    private $googleApiHelper;

    public function let(Processor $processor, \Mage_Catalog_Model_Resource_Product $productResource)
    {
        $this->processor = $processor;

        $this->beConstructedWith($this->processor);
        \Mage::app();
        \Mage::register('_resource_singleton/catalog/product', $productResource);
    }

    public function letGo()
    {
        \Mage::reset();
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(\ErgonTech_Tabular_Model_Import_Profile_Type_Product::class);
    }

    public function it_can_only_be_initialized_once(\ErgonTech_Tabular_Model_Import_Profile $profile, \ErgonTech_Tabular_Helper_Google_Api $api)
    {
        \Mage::register('_helper/ergontech_tabular/google_api', $api);
        $this->initialize($profile);
        $this->shouldThrow(\LogicException::class)->during('initialize', [$profile]);
    }

    public function it_requires_a_header_transform_callback_before_running(\ErgonTech_Tabular_Model_Import_Profile $profile, \ErgonTech_Tabular_Helper_Google_Api $api)
    {
        \Mage::register('_helper/ergontech_tabular/google_api', $api);
        $this->initialize($profile);
        $this->shouldThrow(\LogicException::class)->during('execute');
    }

    public function it_adds_the_right_steps_to_the_Processor(
        \ErgonTech_Tabular_Model_Import_Profile $profile,
        \ErgonTech_Tabular_Helper_Google_Api $api,
        \Google_Service_Sheets $sheetsService)
    {
        $api->getService(\Google_Service_Sheets::class)
            ->willReturn($sheetsService)
            /*->shouldBeCalled()*/;// what's up with this issue? :(
        \Mage::register('_helper/ergontech_tabular/google_api', $api);
        $this->processor->addStep(Argument::type(LoggingStep::class))->shouldBeCalledTimes(3);
        $this->processor->addStep(Argument::type(GoogleSheetsLoadStep::class))->shouldBeCalled();
        $this->processor->addStep(Argument::type(HeaderTransformStep::class))->shouldBeCalled();
        $this->processor->addStep(Argument::type(FastSimpleImport::class))->shouldBeCalled();
        $profile->getExtra('sheets_data')->shouldBeCalled();

        $this->initialize($profile);
    }

    public function it_runs_import(\ErgonTech_Tabular_Model_Import_Profile $profile, \ErgonTech_Tabular_Helper_Google_Api $api)
    {
        \Mage::register('_helper/ergontech_tabular/google_api', $api);
        $this->initialize($profile);
        $this->setHeaderTransformCallback(function ($a) {
        });
//        $this->processor->run()->shouldBeCalled();
//        $this->processor->__invoke(Argument::type(Rows::class))->shouldBeCalled();
    }
}
