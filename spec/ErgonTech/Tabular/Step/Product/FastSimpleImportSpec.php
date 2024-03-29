<?php

namespace spec\ErgonTech\Tabular\Step\Product;

use ErgonTech\Tabular\Rows;
use ErgonTech\Tabular\Step;
use ErgonTech\Tabular\StepExecutionException;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class FastSimpleImportSpec extends ObjectBehavior
{
    private $rowsReturner;

    function let(\AvS_FastSimpleImport_Model_Import $import)
    {
        $this->rowsReturner = function ($rows) { return $rows; };
        $this->beConstructedWith($import);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(Step\Product\FastSimpleImport::class);
    }

    function it_is_a_step()
    {
        $this->shouldHaveType(Step::class);
    }

    function it_returns_rows_upon_invocation(Rows $rows)
    {
        $this->__invoke($rows, $this->rowsReturner)->shouldReturnAnInstanceOf(Rows::class);
    }

    function it_catches_exceptions_during_import_and_turns_them_into_step_exceptions(\AvS_FastSimpleImport_Model_Import $import, Rows $rows)
    {
        $this->beConstructedWith($import);
        $import->processProductImport(Argument::type('array'))->willThrow(new \Exception('There was a problem'));

        $this->shouldThrow(StepExecutionException::class)->during('__invoke', [$rows, $this->rowsReturner]);
    }

}
