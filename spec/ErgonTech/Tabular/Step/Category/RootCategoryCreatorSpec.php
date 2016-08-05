<?php

namespace spec\ErgonTech\Tabular\Step\Category;

use ErgonTech\Tabular\Rows;
use ErgonTech\Tabular\Step;
use ErgonTech\Tabular\Step\Category\RootCategoryCreator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class RootCategoryCreatorSpec extends ObjectBehavior
{
    /**
     * @var \Mage_Catalog_Model_Resource_Category
     */
    protected $categoryResource;
    /**
     * @var \Mage_Catalog_Model_Resource_Category_Collection
     */
    private $collection;

    function let(
        \Mage_Catalog_Model_Resource_Category_Collection $collection,
        \Mage_Catalog_Model_Resource_Category $categoryResource)
    {
        $this->categoryResource = $categoryResource;
        \Mage::unregister('_resource_singleton/catalog/category');
        \Mage::register('_resource_singleton/catalog/category', $this->categoryResource->getWrappedObject());
        $this->collection = $collection;
        $this->beConstructedWith($collection, '_root');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(RootCategoryCreator::class);
    }

    function it_is_a_step()
    {
        $this->shouldHaveType(Step::class);
    }

    function it_loads_categories_with_the_given_names(
        Rows $rows,
        \Varien_Db_Select $select,
        \Varien_Db_Adapter_Interface $adapter)
    {
        $rows->getColumnHeaders()->willReturn(['_category', '_root']);
        $rows->getRows()->willReturn([['foo', 'root1'], ['bar', 'root2']]);

        $this->collection->addAttributeToFilter('name', ['in' => ['root1', 'root2']])
            ->willReturn($this->collection)
            ->shouldBeCalled();

        $this->categoryResource->beginTransaction()->willReturn(null);
        $this->categoryResource->addCommitCallback(Argument::type('callable'))
            ->willReturn($this->categoryResource);

        $this->categoryResource->getIdFieldName()
            ->willReturn('pbbth');

        $this->categoryResource->save(Argument::type(\Mage_Catalog_Model_Category::class))
            ->willReturn(null)
            ->shouldBeCalled();

        $this->categoryResource->commit()
            ->shouldBeCalled()
            ->willReturn(null);

        $this->collection->getData()
            ->willReturn([
                [
                    'entity_id' => '1',
                    'entity_type_id' => '3',
                    'attribute_set_id' => '0',
                    'parent_id' => '0',
                    'created_at' => '0000-00-00 00:00:00',
                    'updated_at' => '2010-05-01 03:27:04',
                    'path' => '1',
                    'position' => '0',
                    'level' => '0',
                    'children_count' => '0',
                    'name' => 'root1',
                ],
            ]);


        $this->__invoke($rows, function ($rows) {
            return $rows;
        });
    }
}
