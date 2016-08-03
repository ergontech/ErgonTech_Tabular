<?php

namespace spec\ErgonTech\Tabular\Command;

use ErgonTech\Tabular\Command\RunProfileCommand;
use Mage;
use N98\Magento\Application;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RunProfileCommandSpec extends ObjectBehavior
{
    /**
     * @var Application
     */
    protected $app;

    function let(Application $app, HelperSet $helperSet, InputDefinition $inputDefinition, InputOption $inputOption, InputArgument $inputArgument)
    {
        $inputDefinition->getOptions()->willReturn([$inputOption]);
        $inputDefinition->getArguments()->willReturn([$inputArgument]);

        $this->app = $app;
        $this->app->getHelperSet()->willReturn($helperSet);
        $this->app->getDefinition()->willReturn($inputDefinition);
        $this->setApplication($this->app);
        Mage::app();
    }

    function letGo()
    {
        Mage::reset();
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(RunProfileCommand::class);
    }

    function it_offers_help()
    {
        /** @var $this RunProfileCommandSpec|RunProfileCommand */
        $help = <<<HELP
Processes data based on the profile type and data of the specified profile.

A list of profiles is available by running the "tabular:profiles:list" command.
HELP;
        $this->getHelp()->shouldReturn($help);
    }

    function it_runs_an_import_profile_matching_input(
        InputInterface $input,
        OutputInterface $output,
        \Mage_Core_Model_Config $config,
        \ErgonTech_Tabular_Model_Resource_Profile_Collection $collection,
        \Varien_Db_Select $select,
        \ErgonTech_Tabular_Helper_Profile_Type_Factory $profileTypeFactory,
        \ErgonTech_Tabular_Model_Profile $profile,
        \ErgonTech_Tabular_Model_Profile_Type $profileType
    ) {
        $config->getResourceModelInstance('ergontech_tabular/profile_collection', [])
            ->willReturn($collection)
            ->shouldBeCalled();

        $collection->addFieldToFilter('name', 'test profile')
            ->willReturn($collection)
            ->shouldBeCalled();

        $collection->getSelect()
            ->willReturn($select);

        $select->limit(1)
            ->shouldBeCalled();

        $collection->getFirstItem()
            ->willReturn($profile)
            ->shouldBeCalled();

        $mageReflection = new \ReflectionClass(Mage::class);
        /** @var \ReflectionProperty $configRef */
        $configRef = $mageReflection->getProperty('_config');
        $configRef->setAccessible(true);
        $configRef->setValue($mageReflection, $config->getWrappedObject());

        Mage::register('_helper/ergontech_tabular/profile_type_factory', $profileTypeFactory->getWrappedObject());

        $profileTypeFactory->createProfileTypeInstance($profile)
            ->willReturn($profileType)
            ->shouldBeCalled();

        $profileType->execute()->shouldBeCalled();

        $input->bind(Argument::type(InputDefinition::class))->willReturn(null);

        $this->app->detectMagento()
            ->willReturn(null)
            ->shouldBeCalled();
        $this->app->initMagento(Argument::type('bool'))
            ->willReturn(null)
            ->shouldBeCalled();
        $this->app->isMagentoEnterprise()
            ->willReturn(false);
        $this->app->getMagentoRootFolder()
            ->willReturn(__DIR__ . '/../../../../root');
        $this->app->getMagentoMajorVersion()
            ->willReturn(10000);

        $input->isInteractive()
            ->willReturn(false);
        $input->hasArgument('command')
            ->willReturn(true);
        $input->validate()
            ->willReturn(null);
        $input->getArgument('command')
            ->willReturn('tabular:profile:run')
            ->shouldBeCalled();
        $input->getOption('profile-name')
            ->willReturn('test profile')
            ->shouldBeCalled();

        /** @var $this RunProfileCommandSpec|RunProfileCommand */
        $this->run($input, $output);
    }
}
