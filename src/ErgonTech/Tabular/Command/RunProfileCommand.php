<?php

namespace ErgonTech\Tabular\Command;

use Mage;
use N98\Magento\Command\AbstractMagentoCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RunProfileCommand extends AbstractMagentoCommand
{
    protected function configure()
    {
        $this->addOption('profile-name', null, InputOption::VALUE_REQUIRED, 'Run an import with the given profile');
        $this->setHelp(<<<HELP
Processes data based on the profile type and data of the specified profile.

A list of profiles is available by running the "tabular:profiles:list" command.
HELP
        );
    }

    public function __construct()
    {
        parent::__construct('tabular:profile:run');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->detectMagento($output);
            $this->initMagento();
            $profileName = $input->getOption('profile-name');

            $profileCollection = Mage::getResourceModel('ergontech_tabular/profile_collection')
                ->addFieldToFilter('name', $profileName);

            $profileCollection->getSelect()->limit(1);

            $profile = $profileCollection->getFirstItem();

            /** @var \ErgonTech_Tabular_Model_Profile_Type $profileType */
            $profileType = Mage::helper('ergontech_tabular/profile_type_factory')->createProfileTypeInstance($profile);

            $profileType->execute();
        } catch (\Exception $e) {
            $output->write(sprintf('<error>%s</error>',$e->getMessage()));
        }
    }
}
