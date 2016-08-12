<?php

namespace spec\ErgonTech\Tabular\Step\ProductCategorization;

use ErgonTech\Tabular\Rows;
use ErgonTech\Tabular\Step;
use ErgonTech\Tabular\StepExecutionException;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class FastSimpleImportSpec extends ObjectBehavior
{
    private $import;

    function let(\AvS_FastSimpleImport_Model_Import $import)
    {
        $this->import = $import;
        $this->beConstructedWith($import);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(Step\ProductCategorization\FastSimpleImport::class);
    }

    function it_is_a_step()
    {
        $this->shouldHaveType(Step::class);
    }

    function it_returns_rows_upon_invocation(Rows $rows)
    {
        $data = [
            'a' => 'b'
        ];
        $rows->getRowsAssoc()->willReturn($data);
        $this->import->processCategoryProductImport($data)
            ->shouldBeCalled();
        $this->__invoke($rows, function ($rows) { return $rows; })->shouldReturnAnInstanceOf(Rows::class);
    }

    function it_turns_other_exceptions_into_step_exceptions(Rows $rows)
    {
        $this->import->processCategoryProductImport(Argument::any())->willThrow(new \Exception('whoopsie'));
        $this->shouldThrow(StepExecutionException::class)->during('__invoke', [$rows, function () {}]);
    }
}
