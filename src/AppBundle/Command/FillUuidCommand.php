<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class FillUuidCommand extends ContainerAwareCommand {
    
    protected function configure() {
        $this
            ->setName('filluuid:generate')
            ->setDescription('Generates uuid for equipments and talents with null value in this column');
    }
    
    protected function execute(InputInterface $input, OutputInterface $output) {
        $this->getContainer()->get('filluuid')->run();
    }
}