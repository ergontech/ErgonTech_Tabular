<?php

namespace spec;

use ErgonTech\Tabular\Rows;
use ErgonTech\Tabular\Step;
use ErgonTech\Tabular\StepExecutionException;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ErgonTech_Tabular_Model_Step_Product_FastSimpleImportSpec extends ObjectBehavior
{
    private $rowsReturner;

    function let(\AvS_FastSimpleImport_Model_Import $import)
    {
        \Mage::app();
        $this->rowsReturner = function ($rows) { return $rows; };
        $this->beConstructedWith($import);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('ErgonTech_Tabular_Model_Step_Product_FastSimpleImport');
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
