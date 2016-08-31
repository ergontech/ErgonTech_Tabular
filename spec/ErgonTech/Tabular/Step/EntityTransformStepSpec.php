<?php

namespace spec\ErgonTech\Tabular\Step;

use ErgonTech\Tabular\Rows;
use ErgonTech\Tabular\Step;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class EntityTransformStepSpec extends ObjectBehavior
{
    private $entityResource;

    function let(\Mage_Eav_Model_Entity_Abstract $entityResource)
    {
        $this->entityResource = $entityResource;
        $this->beConstructedWith($this->entityResource);
    }

    function it_is_a_step()
    {
        $this->shouldImplement(Step::class);
    }
    
    function it_fails_without_an_entity_resource()
    {
        $this->shouldThrow(\Exception::class)->during('__construct', [new \Varien_Object()]);
    }

    function it_gets_attribute_config_for_each_attribute_in_the_data(
        \Mage_Eav_Model_Entity_Attribute_Abstract $attribute,
        \Mage_Eav_Model_Entity_Attribute_Backend_Array $attrBackend,
        Rows $rows
    )
    {
        $this->entityResource->getAttribute('foo')
            ->willReturn($attribute);

        $attribute->getBackend()
            ->willReturn($attrBackend);

        $rowData = 'bar, bar, bar';
        $header = 'foo';
        $dataIn = [
            $header => $rowData
        ];

        $dataOut = [
            'foo' => ['bar', 'bar', 'bar']
        ];

        $rows->getRowsAssoc()->willReturn([$dataIn]);
        $rows->getRows()->willReturn([$rowData]);
        $rows->getColumnHeaders()->willReturn([$header]);

        $ret = $this->__invoke($rows, function ($x) { return $x; });

        $ret->shouldBeAnInstanceOf(Rows::class);
        $ret->getRowsAssoc()->shouldBe([$dataOut]);
    }
}
