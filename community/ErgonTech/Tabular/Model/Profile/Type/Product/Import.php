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
    }

    /**
     * @param callable $cb
     */
    public function setHeaderTransformCallback(callable $cb)
    {
        $reflect = new ReflectionFunction($cb);

        if ($reflect->getNumberOfRequiredParameters() > 1) {
            throw new InvalidArgumentException('Provided callback must require no more than one argument');
        }

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
        $sheetsData = $profile->getExtra('sheets_data');

        $this->processor->addStep(new LoggingStep(new Psr\Log\NullLogger()));
        $this->processor->addStep(new GoogleSheetsLoadStep(
                $sheets, $sheetsData['sheet_id'], $sheetsData['header_range'], $sheetsData['data_range']));
        $this->processor->addStep(new LoggingStep(new Psr\Log\NullLogger()));
        $this->processor->addStep(new HeaderTransformStep($this->headerTransformCallback));
        $this->processor->addStep(new LoggingStep(new Psr\Log\NullLogger()));
        $this->processor->addStep(new FastSimpleImport(Mage::getModel('fastsimpleimport/import')));

        $this->ready = true;
    }
}
