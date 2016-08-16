<?php

namespace spec\ErgonTech\Tabular;

use Mage;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class Model_Source_Widget_TypeSpec extends ObjectBehavior
{
    /**
     * @var \Mage_Widget_Model_Widget_Instance
     */
    private $instance;

    function let(
        \Mage_Core_Model_Config $config,
        \Mage_Widget_Model_Widget_Instance $instance
    )
    {
        $refMage = new \ReflectionClass(Mage::class);
        $refConfig = $refMage->getProperty('_config');
        $refConfig->setAccessible(true);
        $refConfig->setValue($refMage, $config->getWrappedObject());

        $config->getModelInstance('widget/widget_instance', Argument::type('array'))
            ->willReturn($instance->getWrappedObject());

        $this->instance = $instance;
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(\ErgonTech\Tabular\Model_Source_Widget_Type::class);
    }

    function it_gets_an_option_array_of_available_widget_instance_types()
    {
        $options = [
            [
                'value' => 'asdf',
                'label' => 'asdf'
            ],
            [
                'value' => 'foo',
                'label' => 'bar'
            ]
        ];

        $this->instance->getWidgetsOptionArray()
            ->willReturn($options)
            ->shouldBeCalled();

        $this->toOptionArray()->shouldReturn($options);
    }

    function it_gets_an_option_hash_of_all_widget_instance_types()
    {
        $options = [
            [
                'value' => 'asdf',
                'label' => 'asdf'
            ],
            [
                'value' => 'foo',
                'label' => 'bar'
            ]
        ];

        $optionsTransformed = [
            'asdf' => 'asdf',
            'foo' => 'bar'
        ];

        $this->instance->getWidgetsOptionArray()
            ->willReturn($options)
            ->shouldBeCalled();

        $transformed = $this->toOptionHash();
        $transformed->shouldReturn($optionsTransformed);
    }
}
