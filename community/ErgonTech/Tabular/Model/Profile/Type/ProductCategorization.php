<?php

use ErgonTech\Tabular\Processor;

class ErgonTech_Tabular_Model_Profile_Type_ProductCategorization implements ErgonTech_Tabular_Model_Profile_Type
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
     * @throws \LogicException
     * @throws \ErgonTech\Tabular\StepExecutionException
     */
    public function execute()
    {
        if (!is_callable($this->headerTransformCallback)) {
            throw new LogicException('A header transform callback is required for execution of this profile');
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
            throw new LogicException('This profile can only be initialized one time');
        }
        /** @var ErgonTech_Tabular_Helper_Google_Api $googleHelper */
        $googleHelper = Mage::helper('ergontech_tabular/google_api');

        if (is_null($this->headerTransformCallback)) {
            $callback = Mage::helper('ergontech_tabular/headerTransforms')->getHeaderTransformCallbackForProfile($profile);
            $this->headerTransformCallback = (string)$callback;
        }

        $spreadsheetId = $profile->getExtra('spreadsheet_id');
        $headerNamedRange = $profile->getExtra('header_named_range');
        $dataNamedRange = $profile->getExtra('data_named_range');

        /** @var Mage_Core_Model_Resource_Store_Group_Collection $storeGroups */
        $storeGroups = Mage::getResourceModel('core/store_group_collection');
        $storeGroups->join(['cs' => 'core/store'], 'cs.group_id = main_table.group_id', null);
        $storeGroups->getSelect()->where('cs.store_id in (?)', $profile->getStores());

        $rootCategoryIds = array_column($storeGroups->getData(), 'root_category_id');

        /** @var Mage_Catalog_Model_Resource_Category_Collection $rootCategoryNames */
        $rootCategoryNames = Mage::getResourceModel('catalog/category_collection')
            ->addAttributeToSelect('name')
            ->addIdFilter(array_unique($rootCategoryIds))
            ->getColumnValues('name');

        /** @var \Monolog\Logger $logger */
        $logger = Mage::helper('ergontech_tabular/monolog')->registerLogger('tabular');
        $logger->pushHandler(
            new \Monolog\Handler\StreamHandler(sprintf('%s/log/tabular/%s.log',
                Mage::getBaseDir('var'), $profile->getProfileType())));

        $this->processor->addStep(new \ErgonTech\Tabular\Step\ProductCategorization\FastSimpleImport(Mage::getModel('fastsimpleimport/import')));
        $this->processor->addStep(new \ErgonTech\Tabular\LoggingStep(new \Psr\Log\NullLogger()));
        $this->processor->addStep(new \ErgonTech\Tabular\IteratorStep($rootCategoryNames, '_root'));
        $this->processor->addStep(new \ErgonTech\Tabular\LoggingStep(new \Psr\Log\NullLogger()));
        $this->processor->addStep(new \ErgonTech\Tabular\HeaderTransformStep($this->headerTransformCallback));
        $this->processor->addStep(new \ErgonTech\Tabular\LoggingStep(new \Psr\Log\NullLogger()));
        $this->processor->addStep(new \ErgonTech\Tabular\GoogleSheetsLoadStep(
            $googleHelper->getService(Google_Service_Sheets::class, [Google_Service_Sheets::SPREADSHEETS_READONLY]),
            $spreadsheetId, $headerNamedRange, $dataNamedRange));
        $this->processor->addStep(new \ErgonTech\Tabular\LoggingStep(new \Psr\Log\NullLogger()));

        $this->initialized = true;
    }
}