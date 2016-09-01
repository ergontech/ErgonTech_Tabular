<?php

namespace spec\ErgonTech\Tabular\Command;

use ErgonTech\Tabular\Command\RunProfileCommand;
use ErgonTech\Tabular\Helper_Monolog;
use ErgonTech\Tabular\Helper_Profile_Type_Factory;
use ErgonTech\Tabular\Model_Profile;
use ErgonTech\Tabular\Model_Profile_Type;
use Mage;
use Monolog\Handler\HandlerInterface;
use N98\Magento\Application;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RunProfileCommandSpec extends ObjectBehavior
{
    private $input;
    private $output;
    private $config;
    private $profile;
    private $logger;
    private $profileName;
    private $profileTypeFactory;
    private $monologHelper;
    private $profileType;
    /**
     * @var Application
     */
    private $app;

    function let(
        Application $app,
        HelperSet $helperSet,
        InputDefinition $inputDefinition,
        InputOption $inputOption,
        InputArgument $inputArgument,
        InputInterface $input,
        OutputInterface $output,
        \Mage_Core_Model_Config $config,
        Model_Profile $profile,
        LoggerInterface $logger,
        Helper_Profile_Type_Factory $profileTypeFactory,
        Helper_Monolog $monologHelper,
        Model_Profile_Type $profileType
    ) {
        $inputDefinition->getOptions()->willReturn([$inputOption]);
        $inputDefinition->getArguments()->willReturn([$inputArgument]);


        $this->profileName = 'test profile';
        $this->input = $input;
        $this->output = $output;
        $this->config = $config;
        $this->profile = $profile;
        $this->logger = $logger;
        $this->profileTypeFactory = $profileTypeFactory;
        $this->monologHelper = $monologHelper;
        $this->profileType = $profileType;

        $this->app = $app;
        $this->app->getHelperSet()->willReturn($helperSet);
        $this->app->getDefinition()->willReturn($inputDefinition);

        $this->setApplication($this->app);

        $this->app->detectMagento()
            ->willReturn(null);
        $this->app->initMagento(Argument::type('bool'))
            ->willReturn(null);
        $this->app->isMagentoEnterprise()
            ->willReturn(false);
        $this->app->getMagentoRootFolder()
            ->willReturn(__DIR__ . '/../../../../root');
        $this->app->getMagentoMajorVersion()
            ->willReturn(10000);

        $this->input->bind(Argument::type(InputDefinition::class))->willReturn(null);

        $this->profileTypeFactory->createProfileTypeInstance($this->profile)
            ->willReturn($this->profileType);

        $this->input->isInteractive()
            ->willReturn(false);
        $this->input->hasArgument('command')
            ->willReturn(true);
        $this->input->validate()
            ->willReturn(null);
        $this->input->getArgument('command')
            ->willReturn('tabular:profile:run');
        $this->input->getOption('profile-name')
            ->willReturn($this->profileName);

        $profile->getProfileType()
            ->willReturn('asdf');

        $mageReflection = new \ReflectionClass(Mage::class);
        /** @var \ReflectionProperty $configRef */
        $configRef = $mageReflection->getProperty('_config');
        $configRef->setAccessible(true);
        $configRef->setValue($mageReflection, $this->config->getWrappedObject());

        Mage::register('_helper/ergontech_tabular/profile_type_factory', $this->profileTypeFactory->getWrappedObject());
        Mage::register('_helper/ergontech_tabular/monolog', $this->monologHelper->getWrappedObject());
    }

    function letGo()
    {
        Mage::reset();
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

    function it_runs_an_import_profile_matching_input()
    {
        $this->config->getModelInstance('ergontech_tabular/profile', [])
            ->willReturn($this->profile)
            ->shouldBeCalled();

        $this->profile->loadByName($this->profileName)
            ->willReturn($this->profile)
            ->shouldBeCalled();

        $this->profile->getId()
            ->willReturn(1)
            ->shouldBeCalled();

        $this->monologHelper->getLogger(Argument::type('string'))->willReturn($this->logger);
        $this->monologHelper->pushHandler(Argument::type('string'), Argument::type(HandlerInterface::class))->shouldBeCalled();

        $this->profileTypeFactory->createProfileTypeInstance($this->profile)
            ->shouldBeCalled();

        $this->profileType->execute()->shouldBeCalled();

        /** @var $this RunProfileCommandSpec|RunProfileCommand */
        $this->run($this->input, $this->output);
    }

    function it_sends_a_message_to_output_when_the_profile_does_not_exist()
    {
        $this->config->getModelInstance('ergontech_tabular/profile', [])
            ->willReturn($this->profile)
            ->shouldBeCalled();

        $this->profile->getId()
            ->willReturn(null)
            ->shouldBeCalled();

        $this->profile->loadByName($this->profileName)
            ->willReturn($this->profile)
            ->shouldBeCalled();

        $this->profileTypeFactory->createProfileTypeInstance($this->profile)
            ->shouldNotBeCalled();

        $this->profileType->execute()
            ->shouldNotBeCalled();

        $this->output->write('<error>A profile named ' . $this->profileName . ' was not found</error>')
            ->shouldBeCalled();

        $this->run($this->input, $this->output);
    }
}
