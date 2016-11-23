<?php

namespace ErgonTech\Tabular;

use ErgonTech\Tabular\Step\ProductLinkSaveStep;
use Google_Service_Sheets as SheetsService;
use Mage;
use Monolog\Logger;

class Model_Profile_Type_Product_Link_Import implements Model_Profile_Type
{
    /**
     * @var bool
     */
    private $initialized = false;

    /**
     * @var Processor
     */
    private $processor;

    public function __construct(Processor $processor)
    {
        $this->processor = $processor;
    }

    /**
     * Run the profile
     *
     * @return void
     */
    public function execute()
    {
        if (!$this->initialized) {
            throw new Exception_Profile('The profile must first be intialized');
        }
        $this->processor->run();
    }

    /**
     * Initialize the profile type with the given profile instance
     *
     * @param Model_Profile $profile
     * @return void
     */
    public function initialize(Model_Profile $profile)
    {
        if ($this->initialized) {
            throw new Exception_Profile('The profile must be intialized only once');
        }

        /** @var Logger $logger */
        $logger = Mage::helper('ergontech_tabular/monolog')->registerLogger($profile->getProfileType());
        $logger->pushHandler(
            new \Monolog\Handler\StreamHandler(sprintf('%s/log/tabular/%s/%s.log',
                Mage::getBaseDir('var'), $profile->getProfileType(), $profile->getName())));

        /** @var callable $headerTransformer */
        $headerTransformer = Mage::helper('ergontech_tabular/headerTransforms')->getHeaderTransformCallbackForProfile($profile);

        // Save product links
        $this->processor->addStep(new ProductLinkSaveStep(
            Mage::getResourceModel('catalog/product_link'), $profile->getExtra('link_type')));
        $this->processor->addStep(new LoggingStep($logger));

        // Transform each row
        $this->processor->addStep(new RowsTransformStep(
            Mage::helper('ergontech_tabular/rowTransforms')->getRowTransformCallbackForProfile($profile)));
        $this->processor->addStep(new LoggingStep($logger));

        // Merge rows with common entity IDs
        $this->processor->addStep(new MergeStep($headerTransformer($profile->getExtra('product_column'))));
        $this->processor->addStep(new LoggingStep($logger));

        // Transform column headers
        $this->processor->addStep(new HeaderTransformStep($headerTransformer));
        $this->processor->addStep(new LoggingStep($logger));

        // Read data from Google Sheets
        $this->processor->addStep(new GoogleSheetsLoadStep(
            Mage::helper('ergontech_tabular/google_api')->getService(SheetsService::class, [SheetsService::SPREADSHEETS_READONLY]),
            $profile->getExtra('spreadsheet_id'),
            $profile->getExtra('header_named_range'),
            $profile->getExtra('data_named_range')));
        $this->processor->addStep(new LoggingStep($logger));

        $this->initialized = true;
    }
}