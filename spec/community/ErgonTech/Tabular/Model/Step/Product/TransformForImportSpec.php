<?php

namespace spec;

use ErgonTech\Tabular\Rows;
use ErgonTech\Tabular\TransformStep;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ErgonTech_Tabular_Model_Step_Product_TransformForImportSpec extends ObjectBehavior
{
    private $rowReturner;
    private $inRows;

    function let(Rows $rows)
    {
        $rows->getColumnHeaders()->willReturn([
            'Style', 'SKU', 'FANCY FEATURES'
        ]);
        $rows->getRows()->willReturn([
            ['stylish', 'skuy', 'fanciest feature'],
            ['stylish2', 'skuy2', 'fanciest other feature']
        ]);

        $this->inRows = $rows;
        $this->rowReturner = function ($row) {
            return $row;
        };
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(\ErgonTech_Tabular_Model_Step_Product_TransformForImport::class);
    }

    function it_is_a_transform_step()
    {
        $this->shouldHaveType(TransformStep::class);
    }

    function it_transforms_column_headers()
    {
        $this->getMappedColumnHeader('Style')->shouldReturn('style');
        $this->getMappedColumnHeader('FANCY FEATURES')->shouldReturn('fancy_features');
        $this->getMappedColumnHeader('Three Word Header')->shouldReturn('three_word_header');
    }

    function it_does_not_mess_with_row_values()
    {
        $outRows = $this->__invoke($this->inRows, $this->rowReturner);

        $outRows->shouldBeAnInstanceOf(Rows::class);
        $outRows->getRows()->shouldEqual($this->inRows->getWrappedObject()->getRows());
    }

    function it_can_retrieve_row_column_values_by_new_header_after_invocation()
    {
        $outRows = $this->__invoke($this->inRows, $this->rowReturner);

        $outRows->shouldBeAnInstanceOf(Rows::class);
        $rows = $outRows->getRowsAssoc();
        $rows[0]['sku']->shouldReturn('skuy');
    }

}
