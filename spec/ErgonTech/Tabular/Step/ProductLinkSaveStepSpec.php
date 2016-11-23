<?php

namespace spec\ErgonTech\Tabular\Step;

use ErgonTech\Tabular\Model_Profile;
use ErgonTech\Tabular\Rows;
use ErgonTech\Tabular\Step;
use Mage;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use ReflectionClass;

class ProductLinkSaveStepSpec extends ObjectBehavior
{
    /**
     * @var \Mage_Catalog_Model_Resource_Product_Link
     */
    private $linkRes;

    /**
     * @var Rows
     */
    private $rows;

    /**
     * @var callable
     */
    private $next;

    /**
     * @var Model_Profile
     */
    private $profile;

    function let(
        \Mage_Catalog_Model_Product $product,
        \Mage_Catalog_Model_Resource_Product_Link $linkRes,
        \Mage_Catalog_Model_Resource_Product_Collection $collection,
        \Mage_Core_Model_Config $config,
        Rows $rows,
        Model_Profile $profile,
        SaveStepNext $next
    ) {
        $refMage = new ReflectionClass(Mage::class);
        $refConfig = $refMage->getProperty('_config');
        $refConfig->setAccessible(true);
        $refConfig->setValue($config->getWrappedObject());

        $config->getResourceModelInstance('catalog/product_collection', Argument::type('array'))
            ->willReturn($collection);

        $collection->addIdFilter(Argument::type('array'))
            ->willReturn($collection);

        $collection->getItemById(Argument::type('numeric'))
            ->willReturn($product);

        $this->profile = $profile;
        $this->next = $next;
        $this->rows = $rows;
        $this->rows->getRowsAssoc()->willReturn([
            [
                'entity_id' => '123',
                'data' => []
            ]
        ]);
        $this->linkRes = $linkRes;
        $this->beConstructedWith($linkRes, \Mage_Catalog_Model_Product_Link::LINK_TYPE_RELATED);
    }

    function letGo()
    {
        Mage::reset();
    }

    function it_is_a_step()
    {
        $this->beAnInstanceOf(Step::class);
    }

    function it_takes_a_link_resource_during_construct()
    {
        $this->shouldNotThrow()
            ->during('__construct', [
                $this->linkRes,
                \Mage_Catalog_Model_Product_Link::LINK_TYPE_RELATED
            ]);

        $this->shouldThrow()->during('__construct', [
            null,
            \Mage_Catalog_Model_Product_Link::LINK_TYPE_RELATED
        ]);
    }

    function it_takes_a_link_type_during_construct()
    {
        $this->shouldNotThrow()
            ->during('__construct', [
                $this->linkRes,
                \Mage_Catalog_Model_Product_Link::LINK_TYPE_RELATED
            ]);

        $this->shouldThrow()->during('__construct', [
            $this->linkRes,
            null
        ]);
    }

    function it_calls_next_invoked()
    {
        $this->__invoke($this->rows, $this->next);
        $this->next->__invoke(Argument::type(Rows::class))->shouldHaveBeenCalled();
    }

    function it_saves_product_links()
    {
        $this->linkRes->saveProductLinks(
            Argument::type(\Mage_Catalog_Model_Product::class),
            Argument::type('array'),
            Argument::type('int')
        )->shouldBeCalled();

        $this->__invoke($this->rows, $this->next);
    }
    function it_throws_when_not_supplied_with_valid_data()
    {
        $this->rows->getRowsAssoc()->willReturn([
            [
                'data' => []
            ]
        ]);
        $this->shouldThrow()->during('__invoke', [$this->rows, $this->next]);

        $this->rows->getRowsAssoc()->willReturn([
            [
                'entity_id' => '123'
            ]
        ]);
        $this->shouldThrow()->during('__invoke', [$this->rows, $this->next]);
    }
}

// Fake "next" callable for ensuring it gets called
class SaveStepNext
{
    public function __invoke($x) { return $x; }
}

