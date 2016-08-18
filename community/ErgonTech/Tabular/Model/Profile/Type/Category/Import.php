<?php

namespace ErgonTech\Tabular;

use ErgonTech\Tabular\Processor;
use ErgonTech\Tabular\Step\ProfileStoresToRootCategoriesIterator;
use Mage;

class Model_Profile_Type_Category_Import implements Model_Profile_Type
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
     * Run the processor steps
     *
     * @throws Exception_Profile
     */
    public function execute()
    {
        if (!$this->initialized) {
            throw new Exception_Profile('This profile must be initialized before it can be executed');
        }

        $this->processor->run();
    }

    /**
     * Initialize the profile type with the given profile instance
     *
     * @param Model_Profile $profile
     * @return void
     * @throws Exception_Profile
     */
    public function initialize(Model_Profile $profile)
    {
        if ($this->initialized) {
            throw new Exception_Profile('May only initialize the profile one time!');
        }

        $this->headerTransformCallback = Mage::helper('ergontech_tabular/headerTransforms')
            ->getHeaderTransformCallbackForProfile($profile);

        /** @var \Monolog\Logger $logger */
        $logger = Mage::helper('ergontech_tabular/monolog')->registerLogger('tabular');
        $logger->pushHandler(
            new \Monolog\Handler\StreamHandler(sprintf('%s/log/tabular/%s.log',
                Mage::getBaseDir('var'), $profile->getProfileType())));

        $this->processor->addStep(new Step\Category\FastSimpleImport(Mage::getModel('fastsimpleimport/import')));
        $this->processor->addStep(new LoggingStep($logger));
        $this->processor->addStep(new ProfileStoresToRootCategoriesIterator('_root', $profile));
        $this->processor->addStep(new LoggingStep($logger));
        $this->processor->addStep(new HeaderTransformStep($this->headerTransformCallback));
        $this->processor->addStep(new LoggingStep($logger));
        $this->processor->addStep(new GoogleSheetsLoadStep(
            Mage::helper('ergontech_tabular/google_api')->getService(\Google_Service_Sheets::class, [\Google_Service_Sheets::SPREADSHEETS_READONLY]),
            $profile->getExtra('spreadsheet_id'),
            $profile->getExtra('header_named_range'),
            $profile->getExtra('data_named_range')));
        $this->processor->addStep(new LoggingStep($logger));

        $this->initialized = true;
    }
}
