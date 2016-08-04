<?php

use ErgonTech\Tabular\GoogleSheetsLoadStep;
use ErgonTech\Tabular\HeaderTransformStep;
use ErgonTech\Tabular\LoggingStep;
use ErgonTech\Tabular\Processor;
use ErgonTech\Tabular\Step\Product\FastSimpleImport;

class ErgonTech_Tabular_Model_Profile_Type_Product_Import implements ErgonTech_Tabular_Model_Profile_Type
{

    /**
     * @var Processor
     */
    protected $processor;

    /**
     * @var callable
     */
    protected $headerTransformCallback;

    /**
     * @var bool
     */
    protected $ready = false;


    /**
     * ErgonTech_Tabular_Model_Profile_Type_Product constructor.
     * @param Processor $processor
     */
    public function __construct(Processor $processor)
    {
        $this->processor = $processor;
    }

    /**
     * Run the processor
     */
    public function execute()
    {
        if (!$this->ready) {
            throw new LogicException('Must initialize first!');
        }

        if (!is_callable($this->headerTransformCallback)) {
            throw new LogicException('Must set header transformation callback first');
        }

        $this->processor->run();
    }

    /**
     * @param callable $cb
     */
    public function setHeaderTransformCallback(callable $cb)
    {
        // TODO: This  would be nice to reintroduce
//        $reflect = new ReflectionFunction($cb);
//
//        if ($reflect->getNumberOfRequiredParameters() > 1) {
//            throw new InvalidArgumentException('Provided callback must require no more than one argument');
//        }

        $this->headerTransformCallback = $cb;
    }

    /**
     * Initialize the profile type with the given profile instance
     *
     * @param ErgonTech_Tabular_Model_Profile $profile
     * @return void
     */
    public function initialize(ErgonTech_Tabular_Model_Profile $profile)
    {
        if ($this->ready) {
            throw new LogicException('Initialize can only be called once');
        }

        $sheets = Mage::helper('ergontech_tabular/google_api')->getService(Google_Service_Sheets::class);
        /** @var array $sheetsData */
        if (is_null($this->headerTransformCallback)) {
            $extraClass = Mage::getConfig()->getNode(sprintf('%s/%s/extra/header_transform_callback/options/%s/callback',
                ErgonTech_Tabular_Model_Source_Profile_Type::CONFIG_PATH_PROFILE_TYPE,
                $profile->getProfileType(),
                $profile->getExtra('header_transform_callback')));
            $this->setHeaderTransformCallback((string)$extraClass);
        }

        $this->processor->addStep(new FastSimpleImport(Mage::getModel('fastsimpleimport/import')));
        $this->processor->addStep(new LoggingStep(new Psr\Log\NullLogger()));
        $this->processor->addStep(new HeaderTransformStep($this->headerTransformCallback));
        $this->processor->addStep(new LoggingStep(new Psr\Log\NullLogger()));
        $this->processor->addStep(new GoogleSheetsLoadStep(
            $sheets,
            $profile->getExtra('spreadsheet_id'),
            $profile->getExtra('header_named_range'),
            $profile->getExtra('data_named_range')));
        $this->processor->addStep(new LoggingStep(new Psr\Log\NullLogger()));

        $this->ready = true;
    }
}
