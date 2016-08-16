<?php

namespace spec\ErgonTech\Tabular;

use Monolog\Handler\HandlerInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;

class Helper_MonologSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(\ErgonTech\Tabular\Helper_Monolog::class);
    }

    function it_registers_a_logger_by_alias()
    {
        $this->registerLogger('logger')->shouldReturnAnInstanceOf(LoggerInterface::class);
    }

    function it_requires_logger_aliases_be_unique()
    {
        $this->registerLogger('logger');
        $this->shouldThrow(\LogicException::class)->during('registerLogger', ['logger']);
    }

    function it_can_add_a_handler_to_a_logger(
        HandlerInterface $handler
    ) {
        $this->registerLogger('logger');
        $this->pushHandler('logger', $handler);
    }

    function it_throws_when_the_logger_does_not_exist(
        HandlerInterface $handler
    ) {
        $this->shouldThrow(\LogicException::class)->during('pushHandler', ['logger', $handler]);
    }

    function it_can_return_a_logger()
    {
        $this->registerLogger('logger');
        $this->getLogger('logger')->shouldReturnAnInstanceOf(LoggerInterface::class);
    }

    function it_returns_null_if_the_logger_does_not_exist()
    {
        $this->getLogger('logger')->shouldReturn(null);
    }


}
