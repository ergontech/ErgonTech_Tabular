<?php

namespace spec\ErgonTech\Tabular\Step;

use ErgonTech\Tabular\Rows;
use ErgonTech\Tabular\Step;
use ErgonTech\Tabular\StepExecutionException;
use Mage;
use Mage_Core_Model_Config;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class EntitySaveStepSpec extends ObjectBehavior
{
    private $entity;

    private $rows;

    private $next;

    function let(
        Mage_Core_Model_Config $configModel, \Mage_Core_Model_Abstract $entity, Rows $rows, MyNext $next
    ) {
        $refMage = new \ReflectionClass(Mage::class);
        $refConfig = $refMage->getProperty('_config');
        $refConfig->setAccessible(true);
        $refConfig->setValue($refMage, $configModel->getWrappedObject());
        $this->beConstructedWith('foo/bar');

        $rows->getRowsAssoc()
            ->willReturn([
                ['fizz' => 'buzz']
            ]);

        $rows->getColumnHeaders()
            ->willReturn(['fizz']);

        $rows->getRows()
            ->willReturn([
                ['buzz']
            ]);

        $this->rows = $rows;
        $this->next = $next;
        $this->next->__invoke(Argument::type(Rows::class))->will(function ($args) { return $args[0]; });
        $entity->setData(Argument::type('array'))->willReturn($entity);
        $entity->save()->willReturn($entity);
        $entity->getData()->willReturn([]);
        $this->entity = $entity;
        $configModel->getModelInstance('foo/bar', Argument::type('array'))->willReturn($this->entity->getWrappedObject());
        $configModel->getModelInstance(null, Argument::type('array'))->willReturn(false);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(Step\EntitySaveStep::class);
    }

    function it_is_a_step()
    {
        $this->shouldImplement(Step::class);
    }

    function it_requires_a_valid_entity_alias()
    {
        $classId = null;
        $this->shouldThrow(StepExecutionException::class)->during('__construct', [$classId]);
    }

    function it_returns_rows_when_invoked()
    {
        $rows = $this->__invoke($this->rows, $this->next);
        $this->next->__invoke(Argument::type(Rows::class))->shouldBeCalled();

        $rows->shouldBeAnInstanceOf(Rows::class);
    }

    function it_initializes_and_saves_each_entity(Rows $rows)
    {
        $this->entity->setData(['fizz' => 'buzz'])->shouldBeCalled();
        $this->entity->save()->shouldBeCalled();
        $this->entity->getData()->willReturn([
            'pbbth_id' => 1,
            'fizz' => 'buzz'
        ]);
        $this->next->__invoke(Argument::type(Rows::class))->shouldBeCalled();


        $returnedRows = $this->__invoke($this->rows, $this->next);

        $returnedRows->shouldHaveType(Rows::class);
        $returnedRows->getRows()->shouldReturn([[1, 'buzz']]);
        $returnedRows->getColumnHeaders()->shouldReturn(['pbbth_id', 'fizz']);
    }


    function it_does_not_ever_call_save_if_the_array_is_empty(Rows $rows)
    {
        $this->next->__invoke(Argument::type(Rows::class))->shouldBeCalled();

        $rows->getRows()->willReturn([]);
        $this->entity->save()->shouldNotBeCalled();
        $this->__invoke($rows, $this->next);
    }
}

// Fake "next" callable for ensuring it gets called
class MyNext
{
    public function __invoke($x) { return $x; }
}
