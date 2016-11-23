<?php

namespace ErgonTech\Tabular\Step;

use ErgonTech\Tabular\Exception_Profile;
use ErgonTech\Tabular\Rows;
use ErgonTech\Tabular\Step;
use ErgonTech\Tabular\StepExecutionException;
use Exception;
use Mage;
use Mage_Catalog_Model_Product_Link as ProductLink;
use Mage_Catalog_Model_Resource_Product_Link as ProductLinkResource;

class ProductLinkSaveStep implements Step
{
    /**
     * List of valid link types
     * Unfortunately, these values aren't configured by Magento anywhere
     * At least not in a "here's a list of valid types" format
     * So we have to assume no new ones exist! :(
     * @var array
     */
    private $validLinkTypes = [
        ProductLink::LINK_TYPE_RELATED,
        ProductLink::LINK_TYPE_GROUPED,
        ProductLink::LINK_TYPE_UPSELL,
        ProductLink::LINK_TYPE_CROSSSELL
    ];

    /**
     * @var int
     */
    private $linkType;

    /**
     * @var ProductLinkResource
     */
    private $linkResource;

    /**
     * @param ProductLinkResource $linkResource
     * @param int $linkType
     * @throws Exception
     */
    public function __construct(ProductLinkResource $linkResource, $linkType)
    {
        if (!in_array($linkType, $this->validLinkTypes)) {
            throw new StepExecutionException('A valid link type is required');
        }

        $this->linkResource = $linkResource;
        $this->linkType = $linkType;
    }

    /**
     * Accepts a Rows object and returns a rows object
     *
     * @param \ErgonTech\Tabular\Rows $rows
     * @param callable $next
     * @return Rows
     * @throws StepExecutionException
     */
    public function __invoke(Rows $rows, callable $next)
    {
        $products = Mage::getResourceModel('catalog/product_collection')
            ->addIdFilter(array_column($rows->getRowsAssoc(), 'entity_id'));

        array_map(function ($row) use ($products) {

            if (!array_key_exists('entity_id', $row) ||
                !array_key_exists('data', $row)
            ) {
                throw new StepExecutionException(
                    sprintf('Both sku and data must be provided! Received %s',
                        json_encode(array_keys($row))));
            }

            $product = $products->getItemById($row['entity_id']);

            if (!$product instanceof \Mage_Catalog_Model_Product) {
                throw new StepExecutionException(
                    'Supplied "product" must be an instance of Mage_Catalog_Model_Product');
            }

            $this->linkResource->saveProductLinks(
                $product,
                $row['data'],
                $this->linkType
            );
        }, $rows->getRowsAssoc());

        return $next($rows);
    }
}
