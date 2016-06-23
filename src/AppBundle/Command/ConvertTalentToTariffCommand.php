<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ConvertTalentToTariffCommand extends ContainerAwareCommand {
    
    protected function configure() {
        $this
            ->setName('talent:convert-to-tariff')
            ->setDescription('Converts Talent to TalentTariff (use only once when switching to tariffs!)');
    }
    
    protected function execute(InputInterface $input, OutputInterface $output) {
        $text = $this->getContainer()->get('convert_talent_to_tariff')->run();
        $output->writeln($text);
    }
}