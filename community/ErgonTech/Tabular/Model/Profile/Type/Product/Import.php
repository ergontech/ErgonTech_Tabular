<?php

namespace ErgonTech\Tabular;

use ErgonTech\Tabular\Step\EntityTransformStep;
use ErgonTech\Tabular\Step\Product\FastSimpleImport;
use Google_Service_Sheets;
use LogicException;
use Mage;

class Model_Profile_Type_Product_Import implements Model_Profile_Type
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
     * @var callable
     */
    protected $rowTransformCallback;

    /**
     * @var bool
     */
    protected $ready = false;


    /**
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
     * @param Model_Profile $profile
     * @return void
     * @throws \LogicException
     */
    public function initialize(Model_Profile $profile)
    {
        if ($this->ready) {
            throw new LogicException('Initialize can only be called once');
        }

        /** @var Helper_Google_Api $googleHelper */
        $googleHelper = Mage::helper('ergontech_tabular/google_api');

        $this->headerTransformCallback = Mage::helper('ergontech_tabular/headerTransforms')
            ->getHeaderTransformCallbackForProfile($profile);

        $this->rowTransformCallback = Mage::helper('ergontech_tabular/rowTransforms')
            ->getRowTransformCallbackForProfile($profile);

        /** @var \Monolog\Logger $logger */
        $logger = Mage::helper('ergontech_tabular/monolog')->registerLogger($profile->getProfileType());
        $logger->pushHandler(
            new \Monolog\Handler\StreamHandler(sprintf('%s/log/tabular/%s/%s.log',
                Mage::getBaseDir('var'), $profile->getProfileType(), $profile->getName())));

        $this->processor->addStep(new FastSimpleImport(Mage::getModel('fastsimpleimport/import')));
        $this->processor->addStep(new LoggingStep($logger));
        $this->processor->addStep(new RowsTransformStep($this->rowTransformCallback));
        $this->processor->addStep(new LoggingStep($logger));
        $this->processor->addStep(new EntityTransformStep(Mage::getResourceModel('catalog/product')));
        $this->processor->addStep(new LoggingStep($logger));
        $this->processor->addStep(new HeaderTransformStep($this->headerTransformCallback));
        $this->processor->addStep(new LoggingStep($logger));
        $this->processor->addStep(new GoogleSheetsLoadStep(
            $googleHelper->getService(Google_Service_Sheets::class, [Google_Service_Sheets::SPREADSHEETS_READONLY]),
            $profile->getExtra('spreadsheet_id'),
            $profile->getExtra('header_named_range'),
            $profile->getExtra('data_named_range')));
        $this->processor->addStep(new LoggingStep($logger));

        $this->ready = true;
    }
}
