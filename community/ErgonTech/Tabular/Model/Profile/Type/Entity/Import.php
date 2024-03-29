<?php

namespace ErgonTech\Tabular;


use ErgonTech\Tabular\Step\EntitySaveStep;
use Google_Service_Sheets as GoogleSheets;
use Mage;
use Monolog\Logger;
use Psr\Log\NullLogger;

class Model_Profile_Type_Entity_Import implements Model_Profile_Type
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
     * @var callable
     */
    private $rowTransformCallback;


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
     * Run the profile
     *
     * @return void
     * @throws Exception_Profile
     */
    public function execute()
    {
        if (!$this->initialized) {
            throw new Exception_Profile('Must initialize the profile before execute!');
        }

        $this->processor->run();
    }

    /**
     * Initialize the profile type with the given profile instance
     *
     * @param Model_Profile $profile
     * @throws Exception_Profile
     */
    public function initialize(Model_Profile $profile)
    {
        if ($this->initialized) {
            throw new Exception_Profile('Can only initialize the profile one time');
        }

        $this->headerTransformCallback = Mage::helper('ergontech_tabular/headerTransforms')
            ->getHeaderTransformCallbackForProfile($profile);

        $this->rowTransformCallback = Mage::helper('ergontech_tabular/rowTransforms')
            ->getRowTransformCallbackForProfile($profile);

        /** @var Helper_Google_Api $googleHelper */
        $googleHelper = Mage::helper('ergontech_tabular/google_api');

        $sheetsService = $googleHelper->getService(
            GoogleSheets::class, [GoogleSheets::SPREADSHEETS_READONLY]);
        $spreadsheetId = $profile->getExtra('spreadsheet_id');
        $headerNamedRange = $profile->getExtra('header_named_range');
        $dataNamedRange = $profile->getExtra('data_named_range');

        $classId = (string)Mage::getConfig()->getNode(sprintf('%s/%s/entity',
            Model_Source_Profile_Type::CONFIG_PATH_PROFILE_TYPE,
            $profile->getProfileType()));

        /** @var \Monolog\Logger $logger */
        $logger = Mage::helper('ergontech_tabular/monolog')->registerLogger($profile->getProfileType());
        $logger->pushHandler(
            new \Monolog\Handler\StreamHandler(sprintf('%s/log/tabular/%s/%s.log',
                Mage::getBaseDir('var'), $profile->getProfileType(), $profile->getName())));

        $this->processor->addStep(new EntitySaveStep($classId));
        $this->processor->addStep(new LoggingStep($logger));
        $this->processor->addStep(new RowsTransformStep($this->rowTransformCallback));
        $this->processor->addStep(new LoggingStep($logger));
        $this->processor->addStep(new HeaderTransformStep($this->headerTransformCallback));
        $this->processor->addStep(new LoggingStep($logger));
        $this->processor->addStep(new IteratorStep([$profile->getStores()], 'stores'));
        $this->processor->addStep(new LoggingStep($logger));
        $this->processor->addStep(new GoogleSheetsLoadStep(
            $sheetsService, $spreadsheetId, $headerNamedRange, $dataNamedRange));
        $this->processor->addStep(new LoggingStep($logger));


        $this->initialized = true;
    }
}