<?php

namespace spec\ErgonTech\Tabular\Step;

use ErgonTech\Tabular\IteratorStep;
use ErgonTech\Tabular\Model_Profile;
use ErgonTech\Tabular\Rows;
use ErgonTech\Tabular\Step;
use ErgonTech\Tabular\StepExecutionException;
use Mage;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ProfileStoresToRootCategoriesIteratorSpec extends ObjectBehavior
{
    private $rowsReturner;

    private $storeGroupCollection;

    private $rows;

    function let(
        \Mage_Core_Model_Config $config,
        \Mage_Core_Model_Config_Options $configOptions,
        \Varien_Db_Select $select,
        \Mage_Catalog_Model_Resource_Category_Collection $categoryCollection,
        \Mage_Core_Model_Resource_Store_Group_Collection $storeGroupCollection,
        Model_Profile $profile,
        Rows $rows
    )
    {
        $refMage = new \ReflectionClass(\Mage::class);
        $refConfig = $refMage->getProperty('_config');
        $refConfig->setAccessible(true);
        $refConfig->setValue($config->getWrappedObject());

        $this->rows = $rows;

        $this->rowsReturner = function ($x) {
            return $x;
        };
        $this->storeGroupCollection = $storeGroupCollection;

        $rows->getRows()->willReturn([
            ['asdf', 'asdf'],
            ['asdf', 'asdf']
        ]);
        $rows->getColumnHeaders()->willReturn(['foo', 'bar']);

        $config->getResourceModelInstance('core/store_group_collection', Argument::any())
            ->willReturn($storeGroupCollection);

        $config->getResourceModelInstance('catalog/category_collection', Argument::any())
            ->willReturn($categoryCollection);

        $this->storeGroupCollection->getSelect()
            ->willReturn($select);

        $this->storeGroupCollection->join(Argument::type('array'), Argument::type('string'), Argument::any())
            ->willReturn($this->storeGroupCollection);

        $this->storeGroupCollection->getData()
            ->willReturn([]);

        $categoryCollection->addAttributeToSelect(Argument::type('string'))
            ->willReturn($categoryCollection);

        $categoryCollection->addIdFilter(Argument::type('array'))
            ->willReturn($categoryCollection);

        $categoryCollection->getColumnValues('name')
            ->willReturn(['root category name']);

        $this->beConstructedWith('asdf', $profile);
    }

    function letGo()
    {
        Mage::reset();
    }
    function it_is_initializable()
    {
        $this->shouldHaveType(\ErgonTech\Tabular\Step\ProfileStoresToRootCategoriesIterator::class  );
    }

    function it_is_a_step()
    {
        $this->shouldImplement(Step::class);
    }

    function it_is_an_iteratorStep()
    {
        $this->shouldHaveType(IteratorStep::class);
    }

    function it_returns_rows_upon_invocation(Rows $rows)
    {
        $this->__invoke($rows, $this->rowsReturner)->shouldReturnAnInstanceOf(Rows::class);
    }

    function it_requires_a_column_name_on_init()
    {
        $this->shouldThrow(\Exception::class)->during('__construct', [null]);
    }

    function it_gets_root_categories_upon_init(Model_Profile $profile, Rows $rows)
    {
        $this->storeGroupCollection->getData()->shouldBeCalled();
        $this->beConstructedWith('_root', $profile);
        $this->__invoke($rows, $this->rowsReturner);
    }
}
