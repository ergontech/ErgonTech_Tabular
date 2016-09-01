<?php

namespace ErgonTech\Tabular;

use ErgonTech\Tabular\Processor;
use ErgonTech\Tabular\Step\ProfileStoresToRootCategoriesIterator;
use Google_Service_Sheets;
use LogicException;
use Mage;
use Mage_Catalog_Model_Resource_Category_Collection;
use Mage_Core_Model_Resource_Store_Group_Collection;

class Model_Profile_Type_ProductCategorization implements Model_Profile_Type
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



    public function __construct(Processor $processor)
    {
        $this->processor = $processor;
    }

    /**
     * Run the profile
     *
     * @return void
     * @throws LogicException
     * @throws StepExecutionException
     */
    public function execute()
    {
        if (!$this->initialized) {
            throw new LogicException('A header transform callback is required for execution of this profile');
        }

        $this->processor->run();
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
        if ($this->initialized) {
            throw new LogicException('This profile can only be initialized one time');
        }
        /** @var Helper_Google_Api $googleHelper */
        $googleHelper = Mage::helper('ergontech_tabular/google_api');

        $this->headerTransformCallback = Mage::helper('ergontech_tabular/headerTransforms')
            ->getHeaderTransformCallbackForProfile($profile);

        $spreadsheetId = $profile->getExtra('spreadsheet_id');
        $headerNamedRange = $profile->getExtra('header_named_range');
        $dataNamedRange = $profile->getExtra('data_named_range');

        /** @var \Monolog\Logger $logger */
        $logger = Mage::helper('ergontech_tabular/monolog')->registerLogger($profile->getProfileType());
        $logger->pushHandler(
            new \Monolog\Handler\StreamHandler(sprintf('%s/log/tabular/%s/%s.log',
                Mage::getBaseDir('var'), $profile->getProfileType(), $profile->getName())));

        $this->processor->addStep(new Step\ProductCategorization\FastSimpleImport(Mage::getModel('fastsimpleimport/import')));
        $this->processor->addStep(new LoggingStep($logger));
        $this->processor->addStep(new ProfileStoresToRootCategoriesIterator('_root', $profile));
        $this->processor->addStep(new LoggingStep($logger));
        $this->processor->addStep(new HeaderTransformStep($this->headerTransformCallback));
        $this->processor->addStep(new LoggingStep($logger));
        $this->processor->addStep(new GoogleSheetsLoadStep(
            $googleHelper->getService(Google_Service_Sheets::class, [Google_Service_Sheets::SPREADSHEETS_READONLY]),
            $spreadsheetId, $headerNamedRange, $dataNamedRange));
        $this->processor->addStep(new LoggingStep($logger));

        $this->initialized = true;
    }
}