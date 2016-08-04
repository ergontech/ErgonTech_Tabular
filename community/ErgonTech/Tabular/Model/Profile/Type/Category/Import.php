<?php

use ErgonTech\Tabular\Processor;

class ErgonTech_Tabular_Model_Profile_Type_Category_Import implements ErgonTech_Tabular_Model_Profile_Type
{

    /**
     * @var bool
     */
    private $initialized = false;

    /**
     * @var callable
     */
    private $headerTransformCallback;

    /**
     * @var Processor
     */
    private $processor;

    /**
     * @param Processor $processor
     */
    public function __construct(Processor $processor)
    {
        $this->processor = $processor;
    }

    /**
     * @param callable $callback
     */
    public function setHeaderTransformCallback(callable $callback)
    {
        $this->headerTransformCallback = $callback;
    }

    /**
     * Run the processor steps
     *
     * @throws \LogicException
     */
    public function execute()
    {
        if (!is_callable($this->headerTransformCallback)) {
            throw new LogicException('The header transformation callback must be set and callable before execution!');
        }

        $this->processor->run();
    }

    /**
     * Initialize the profile type with the given profile instance
     *
     * @param ErgonTech_Tabular_Model_Profile $profile
     * @return void
     * @throws \LogicException
     */
    public function initialize(ErgonTech_Tabular_Model_Profile $profile)
    {
        if ($this->initialized) {
            throw new LogicException('May only initialize the profile one time!');
        }

        if (is_null($this->headerTransformCallback)) {
            $callback = Mage::helper('ergontech_tabular/headerTransforms')->getHeaderTransformCallbackForProfile($profile);
            $this->setHeaderTransformCallback((string)$callback);
        }

        $this->processor->addStep(new \ErgonTech\Tabular\LoggingStep(new \Psr\Log\NullLogger()));
        $this->processor->addStep(new \ErgonTech\Tabular\Step\Category\FastSimpleImport(Mage::getModel('fastsimpleimport/import')));
        $this->processor->addStep(new \ErgonTech\Tabular\LoggingStep(new \Psr\Log\NullLogger()));
        $this->processor->addStep(new \ErgonTech\Tabular\Step\Category\RootCategoryCreator(Mage::getResourceModel('catalog/category_collection')));
        $this->processor->addStep(new \ErgonTech\Tabular\LoggingStep(new \Psr\Log\NullLogger()));
        $this->processor->addStep(new \ErgonTech\Tabular\HeaderTransformStep($this->headerTransformCallback));
        $this->processor->addStep(new \ErgonTech\Tabular\LoggingStep(new \Psr\Log\NullLogger()));
        $this->processor->addStep(new \ErgonTech\Tabular\GoogleSheetsLoadStep(
            Mage::helper('ergontech_tabular/google_api')->getService(Google_Service_Sheets::class),
            $profile->getExtra('spreadsheet_id'),
            $profile->getExtra('header_named_range'),
            $profile->getExtra('data_named_range')));

        $this->initialized = true;
    }
}
