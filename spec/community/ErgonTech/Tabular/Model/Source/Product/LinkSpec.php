<?php

namespace spec\ErgonTech\Tabular;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class Model_Source_Product_LinkSpec extends ObjectBehavior
{
    public function it_can_create_an_option_array()
    {
        $optionArray = $this->toOptionArray();
        $optionArray[0]['label']->shouldNotBeNull();
        $optionArray[0]['value']->shouldNotBeNull();
    }

    public function it_has_a_cross_sell_option()
    {
        $optionArray = $this->toOptionArray();
        $optionArray->shouldContain([
            'label' => 'Cross Sells',
            'value' => \Mage_Catalog_Model_Product_Link::LINK_TYPE_CROSSSELL
        ]);
    }

    public function it_has_an_up_sell_option()
    {
        $optionArray = $this->toOptionArray();
        $optionArray->shouldContain([
            'label' => 'Up Sells',
            'value' => \Mage_Catalog_Model_Product_Link::LINK_TYPE_UPSELL
        ]);
    }

    public function it_has_a_grouped_option()
    {
        $optionArray = $this->toOptionArray();
        $optionArray->shouldContain([
            'label' => 'Grouped Products',
            'value' => \Mage_Catalog_Model_Product_Link::LINK_TYPE_GROUPED
        ]);
    }

    public function it_has_a_related_option()
    {
        $optionArray = $this->toOptionArray();
        $optionArray->shouldContain([
            'label' => 'Related Products',
            'value' => \Mage_Catalog_Model_Product_Link::LINK_TYPE_RELATED
        ]);
    }
}
