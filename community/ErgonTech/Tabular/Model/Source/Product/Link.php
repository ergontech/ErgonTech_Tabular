<?php

namespace ErgonTech\Tabular;

class Model_Source_Product_Link
{
    public function toOptionArray()
    {
        return [
            ['label' => 'Cross Sells', 'value' => \Mage_Catalog_Model_Product_Link::LINK_TYPE_CROSSSELL],
            ['label' => 'Up Sells', 'value' => \Mage_Catalog_Model_Product_Link::LINK_TYPE_UPSELL],
            ['label' => 'Related Products', 'value' => \Mage_Catalog_Model_Product_Link::LINK_TYPE_RELATED],
            ['label' => 'Grouped Products', 'value' => \Mage_Catalog_Model_Product_Link::LINK_TYPE_GROUPED],
        ];
    }
}